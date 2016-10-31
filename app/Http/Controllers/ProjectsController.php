<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Factories\UploadFactory;
use App\FileRequest;
use App\Http\Requests\AddProjectFileRequest;
use App\Http\Requests\CreateProjectFolderRequest;
use App\Http\Requests\SaveProjectRequest;
use App\Project;
use App\ProjectFile;
use App\ProjectFolder;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class ProjectsController extends Controller
{

    /**
     * Return projects for authenticated User.
     *
     * @return mixed
     */
    public function getUserProjects()
    {
        return Auth::user()->projects;
    }

    /**
     * Handle request to save a new Project.
     *
     * @param SaveProjectRequest $request
     * @return Project
     */
    public function postSaveNew(SaveProjectRequest $request)
    {
        $project = Project::create($request->all());
        Auth::user()->projects()->save($project, [
            'accepted' => 1,
            'admin' => 1
        ]);
        return $project;
    }

    /**
     * Single Project in JSON
     *
     * @param Project $project
     * @return Model
     */
    public function getSingleProject(Project $project)
    {
        $this->authorize('member', $project);

        return $project->load([
            'folders' => function ($query) {
                $query->orderBy('position', 'asc');
            },
            'folders.files' => function ($query) {
                $query->with('fileRequest')->orderBy('position', 'asc');
            }
        ]);
    }

    /**
     * Request to accept an invitation to join Project.
     *
     * @param Project $project
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function postJoin(Project $project)
    {
        if($project->members->contains(Auth::user())) return response("Already a member.");
        if( ! $project->pendingMembers()->contains(Auth::user())) abort(403, "Oops! We couldn't find your invitation to the requested project. Please ask the admin to re-send your invitation.");
        $project->acceptInvitation(Auth::user());
        return response("Joined project.");
    }

    /**
     * Handle request to make a User manager or demote from manager.
     *
     * @param Project $project
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function postDefineManager(Project $project, Request $request)
    {
        $this->authorize('admin', $project);
        $user = User::findOrFail($request->user_id);
        $project->defineManager($user, $request->manager);
        return response('Defined project manager.');
    }

    /**
     * Single update request to update a Project, ProjectFolder(s) and ProjectFile(s).
     * We batch put updates to increase efficiency and reduce redundant updates as
     * User is most likely performing multiple requests per second.
     *
     * @param Project $project
     * @param Request $request
     * @return Project
     */
    public function putUpdateItems(Project $project, Request $request)
    {
        $this->authorize('member', $project);
        $updatedModels = $request->all();

        if ($updatedProject = $updatedModels['project']) {
            $project->update($updatedProject);
        }

        if ($updatedFolders = $updatedModels['folders']) {
            foreach ($updatedFolders as $id => $updatedFolder) {
                $projectFolder = ProjectFolder::find($id);
                $this->authorize('updateFolder', [$project, $projectFolder]);
                $projectFolder->update($updatedFolder);
            }
        }

        if ($updatedFiles = $updatedModels['files']) {
            foreach ($updatedFiles as $id => $updatedFile) {
                $projectFile = ProjectFile::find($id);
                $this->authorize('updateFile', [$project, $projectFile]);
                $projectFile->update($updatedFile);
            }
        }

        return response('Updated project items.');
    }

    /**
     * Delete Project.
     *
     * @param Project $project
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function delete(Project $project)
    {
        $this->authorize('admin', $project);
        $project->fullDelete();
        return response('Deleted project');
    }

    /**
     * Handle request to create a Project Folder.
     *
     * @param Project $project
     * @param Request $request
     * @return Model
     */
    public function postCreateFolder(Project $project, CreateProjectFolderRequest $request)
    {
        $this->authorize('member', $project);
        return $project->folders()->create($request->all())->load('files');
    }


    /**
     * Delete Project Folder
     *
     * @param Project $project
     * @param ProjectFolder $projectFolder
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteFolder(Project $project, ProjectFolder $projectFolder)
    {
        $this->authorize('updateFolder', [$project, $projectFolder]);
        $projectFolder->fullDelete();
        return response("Deleted project folder");
    }

    /**
     * Create a new File within Project Folder.
     *
     * @param Project $project
     * @param ProjectFolder $projectFolder
     * @param Request $request
     * @return Model
     */
    public function postAddFile(Project $project, ProjectFolder $projectFolder, AddProjectFileRequest $request)
    {
        $this->authorize('addFile', [$project, $projectFolder]);
        return $projectFolder->files()->create($request->all());
    }

    public function getProjectFile(Project $project, ProjectFile $projectFile)
    {
        $this->authorize('viewFile', [$project, $projectFile]);
        return $projectFile->loadAllRelations();
    }

    /**
     * Attaches a FileRequest to a ProjectFile.
     *
     * @param Project $project
     * @param ProjectFile $projectFile
     * @param Request $request
     * @return Model
     */
    public function postAttachFileRequest(Project $project, ProjectFile $projectFile, Request $request)
    {
        $this->authorize('updateFile', [$project, $projectFile]);

        if($fileRequestHash = $request->file_request_hash) {
            // Attaching to a FileRequest
            $fileRequest = FileRequest::findByHash($request->file_request_hash);
            $this->authorize('update', $fileRequest);
            $projectFile->update([
                'file_request_id' => $fileRequest->id
            ]);
        } else {
            // Detaching File Request
            $projectFile->update([
                'file_request_id' => null
            ]);
        }

        return ProjectFile::find($projectFile->id)->fresh()->loadAllRelations();
    }

    /**
     * Directly upload to a ProjectFile.
     *
     * @param Project $project
     * @param ProjectFile $projectFile
     * @param Request $request
     * @return Model
     */
    public function postUploadFile(Project $project, ProjectFile $projectFile, Request $request)
    {
        $this->authorize('updateFile', [$project, $projectFile]);
        $upload = UploadFactory::store($projectFile, $request->file('file'));
        $commentBody = 'Uploaded a <a href="'. awsURL() . $upload->path . '">new file</a>';
        Comment::addComment($projectFile->id, 'App\\ProjectFile', $commentBody, Auth::id());

        // TODO ::: Use pusher so the new comment automatically shows up in thread.

        return $projectFile->loadAllRelations();
    }

    /**
     * Delete Project File.
     *
     * @param Project $project
     * @param ProjectFile $projectFile
     * @return string
     */
    public function deleteProjectFile(Project $project, ProjectFile $projectFile)
    {
        $this->authorize('updateFile', [$project, $projectFile]);
        $projectFile->fullDelete();
        return 'Deleted project file';
    }
}
