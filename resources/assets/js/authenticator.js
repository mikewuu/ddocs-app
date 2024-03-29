/**
 * Client Side Authenticator
 * Handles all authentication methods and properties.
 */

module.exports = {

    // Possible auth/token errors we'll want to deal with
    _authTokenErrors: [
        'unauthenticated',      // default 'auth' middleware failed or didn't get a token
        'token_invalid',
        'token_expired',
        'token_revoked'
    ],

    // Path to redirect to after getting new token.
    _redirectPath: '',

    // Waiting on auth token refresh
    _refreshing: false,

    // Requests that need to be remade when refresh successful
    _remakeQueue: [],

    // Refresh our auth token
    _refreshAuthToken(){
        this._refreshing = true;
        Vue.http.post('/refresh_token', {
            refresh_token: this._getRefreshToken()
        }).then((refreshTokenResponse) => {
            // Successfully refreshed!
            let newToken = 'Bearer ' + refreshTokenResponse.json().token;
            this._storeAuthToken(newToken);
            this._setHeaders(newToken);
            this._refreshing = false;
            vueGlobalEventBus.$emit('refreshed_auth_user');
            this._performRemakeQueue(newToken);
        }, (refreshTokenResponse) => {
            // Failed to refresh token... log out of client.
            this._flushRemakeQueue();
            this._removeRefreshToken();
            this._removeAuthToken();
            this._unsetAuthenticatedUser();
            if (router.currentRoute.meta.requiresAuth) {
                this._redirectToLogin();
            }
        });
    },

    // Loop through and re-make requests in the queue
    _performRemakeQueue(newToken) {
        for (let i = 0; i < this._remakeQueue.length; i++) {
            let item = this._remakeQueue[i];
            item.request.headers['Authorization'] = newToken;
            Vue.http(item.request).then((response) => {
                // tell remakeRequest that the request has been made.
                item.resolve(response);
            });
        }
        // Can't be sure the order in which the requests will complete so
        // no shift'ing them. Will just clear the queue on completion.
        this._flushRemakeQueue();
    },

    // Clear refresh queue
    _flushRemakeQueue() {
        this._remakeQueue = [];
    },

    // Waiting for a refresh request to be handled. Any request made while
    // refreshing returns this promise.
    _remakeRequest(request) {
        return new Promise((resolve, reject) => {
            // Add to remake queue with our resolve func
            this._remakeQueue.push({
                request: request,
                resolve: resolve
            });
        })
    },

    // Redirect User if response is for an invalid/expired token.
    _checkAuthentication(request, response) {
        return new Promise((resolve, reject) => {
            // response hit a token/auth related error
            if (response.status === 401 && this._authTokenErrors.indexOf(response.json().error) !== -1) {
                // If we're not refreshing our token yet, get on it.
                if (! this._refreshing) this._refreshAuthToken();
                // Return a promise that the request will be remade.
                return this._remakeRequest(request).then((newRes) => {
                    // Remade and finished checking authentication.
                    resolve(newRes);
                });

            }
            // if we didn't get any token errors, just pass our response back
            resolve(response);
        });
    },

    // Need User to re-authenticate
    _redirectToLogin(){

        // Clear all pending requests
        RequestsMonitor.flushQueue();

        // save where the user is currently at
        this._redirectPath = router.currentRoute.fullPath;
        router.push('/login');
    },

    // Get our refresh token
    _getRefreshToken(){
        let refreshToken = Cookies.get('ddocs_refresh_token');
        return refreshToken !== 'undefined' ? refreshToken : null;
    },

    /**
     * Return root domain
     *
     * @returns {string}
     * @private
     */
    _getRootDomain() {
        let parts = location.hostname.split('.');
        parts.shift();
        return parts.join('.');
    },

    /**
     * Sets a cookie to be read by client.
     *
     * @param name
     * @param value
     * @private
     */
    _setCookie(name, value) {
        Cookies.set(name, value, {
            expires: 14,
            path: '/',
            domain: this._getRootDomain()
        });
    },

    /**
     * Delete client cookie
     *
     * @param name
     * @private
     */
    _removeCookie(name) {
        Cookies.remove(name, {
            domain: this._getRootDomain()
        });
    },

    // Store our long-life refresh token. Refresh tokens are
    // used to renew auth tokens.
    _storeRefreshToken(token) {
        this._setCookie('ddocs_refresh_token', token);
    },

    // Remove refresh token.
    _removeRefreshToken(){
        this._removeCookie('ddocs_refresh_token');
    },

    // Get auth token
    _getAuthToken() {
        let authToken = Cookies.get('ddocs_auth_token');
        return authToken !== 'undefined' ? authToken : null;
    },

    // Store auth token
    _storeAuthToken(token){
        // store a auth token so it'll be read on refresh
        this._setCookie('ddocs_auth_token', token);
    },

    // Remove our auth token
    _removeAuthToken(){
        this._removeCookie('ddocs_auth_token');
    },

    // Set token in request headers for authentication
    _setHeaders(token) {
        Vue.http.headers.common['Authorization'] = token;
    },

    // Unset token in header so all subsequent requests are
    // made as a guest.
    _unsetHeaders() {
        delete Vue.http.headers.common["Authorization"];
    },

    // Redirect User to saved path before redirect or '/'
    _goRedirectPath(){
        if (this._redirectPath) {
            router.push(this._redirectPath);
            this._redirectPath = '';
        } else {
            router.push('/');
        }
    },

    // HTTP Interceptor that we'll use to catch all responses and pass
    // them onto our auth object to handle token related responses.
    _pushResourceInterceptor(){
        Vue.http.interceptors.push((request, next) => {
            next((response) => {
                return this._checkAuthentication(request, response).then((response) => {
                    return response;
                });
            });
        });
    },

    // Tell Vuex to fetch and set the authenticated user.
    _fetchAuthenticatedUser(){
        store.dispatch('fetchAuthenticatedUser');
    },

    // Unset the user from our Vuex store.
    _unsetAuthenticatedUser(){
        store.commit('setUser', '');
    },

    // Turn client back into a Guest.
    _revertToGuest(){
        this._removeAuthToken();
        this._removeRefreshToken();
        this._unsetHeaders();
        this._unsetAuthenticatedUser();
        router.push('/login');
    },

    // Setup everything up on page-load. This only gets fired once
    // and is called within bootstrap.js
    setup() {

        // Set Interceptor. This must occur first for all subsequent
        // requests to go through the interceptor.
        this._pushResourceInterceptor();

        // User is logged in on page load.
        if (this._getAuthToken()) {
            this._setHeaders(this._getAuthToken());     // Set our request headers
            this._fetchAuthenticatedUser();             // fetch User
        }
    },

    // Check if there is an authenticated User.
    check() {
        return !!this._getAuthToken();
    },

    // Set the path to be redirecte to after subsequent login.
    setRedirectPath(path){
        this._redirectPath = path;
    },

    // Login the user client-side. The response is what we get back
    // from either '/login' or '/register'.
    login(response){

        let authToken = 'Bearer ' + response.token;
        this._storeAuthToken(authToken);
        let refreshToken = response.refresh_token;
        this._storeRefreshToken(refreshToken);

        this._setHeaders(authToken);
        this._fetchAuthenticatedUser();

        this._goRedirectPath();
    },

    // Logout authenticated user
    logout(){
        Vue.http.post('/logout', {
            refresh_token: this._getRefreshToken()
        }).then((res) => {
            this._revertToGuest();
        }, (res) => {
            this._revertToGuest();
        });
    }
};
