<template>
    <nav id="navbar">

        <button type="button" class="btn-toggle-sidebar"
                v-show="! showSidebar && authenticatedUser"
                @click="toggleSidebar"
        >
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>

        <div class="nav-left">
            <div class="logo" v-if="! authenticatedUser">
                <a href="http://ddocs.com" alt="ddocs home page"><img src="/images/logo/logo.svg" alt="ddocs logo"></a>
            </div>
            <div class="navbar-title truncate" v-html="navTitle"></div>
        </div>

        <div class="user-account dropdown" v-show="authenticatedUser">
            <div class="nav-credits badge" data-toggle="tooltip" data-placement="bottom" title="Credits Remaining">
                {{ authenticatedUser.credits }}
            </div>
            <div class="dropdown">
                <a href="#"
                   class="dropdown-toggle link-settings"
                   data-toggle="dropdown"
                   role="button"
                   aria-haspopup="true"
                   aria-expanded="false"
                >
                    <user-avatar :user="authenticatedUser"></user-avatar>
                </a>
                <ul class="dropdown-menu dropdown-menu-right ">
                    <li>
                        <router-link to="/account">Account</router-link>
                    </li>
                    <li>
                        <a class="clickable" @click.prevent="logout">
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <ul id="nav-guest-links" class="list-inline list-unstyled" v-show="! authenticatedUser">
            <li>
                <router-link to="/login">Login</router-link>
            </li>
            <li>
                <router-link to="/register">Sign Up</router-link>
            </li>
        </ul>
    </nav>
</template>
<script>
    export default {
        data: function () {
            return {}
        },
        computed: {
            authenticatedUser(){
                return this.$store.state.authenticatedUser;
            },
            navTitle() {
                return this.$store.state.navTitle;
            },
            showSidebar () {
                return this.$store.state.showSidebar;
            }
        },
        methods: {
            logout(){
                Authenticator.logout();
            },
            toggleSidebar() {
                this.$store.commit('toggleSidebar');
            }
        }
    }
</script>