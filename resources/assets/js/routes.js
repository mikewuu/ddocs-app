module.exports = [
    {
        path: '/',
        redirect: '/checklists'
    },
    {
        path: '/login',
        component: require('./components/auth/Login.vue'),
        meta: {guestOnly: true}
    },
    {
        path: '/register',
        component: require('./components/auth/Register.vue'),
        meta: {guestOnly: true}
    },
    {
        path: '/password/reset',
        component: require('./components/auth/SendResetLink.vue'),
        meta: {guestOnly: true}
    },
    {
        path: '/checklists',
        component: require('./components/checklist/Collection.vue'),
        meta: {requiresAuth: true}
    },
    {
        path: '/password/reset/:token',
        component: require('./components/auth/ResetForm.vue'),
        meta: {guestOnly: true}
    },
    {
        path: '/checklists/make',
        component: require('./components/checklist/Maker.vue'),
        meta: {requiresAuth: true}
    },
    {
        name: 'checklistSingle',
        path: '/c/:checklist_hash/:checklist_name?',
        component: require('./components/checklist/Single.vue')
    },
    {
        path: '/projects',
        component: require('./components/projects/ProjectsList.vue'),
        meta: {requiresAuth: true}
    },
    {
        path: '/projects/:project_id',
        component: require('./components/projects/ProjectBoard.vue'),
        meta: {requiresAuth: true}
    },
    {
        path: '/projects/:project_id/join',
        component: require('./components/projects/JoinProject.vue'),
        meta: {requiresAuth: true}
    },
    {
        path: '/account',
        component: require('./components/account/Overview.vue'),
        meta: {requiresAuth: true}
    },
    {
        path: '/recipients/:recipient_hash/turn_off_notifications',
        component: require('./components/recipients/TurnOffNotifications.vue'),
    },
    {
        path: '/error/404/:type?',
        component: require('./components/errors/404.vue'),
    },
    {
        path: '*',
        redirect: '/'
    },
];

