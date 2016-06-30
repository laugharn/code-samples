<template>
    <div class="container --items --posts">
        <div class="items posts">
            <post :post="post" v-for="post in posts" transition="fade"></post>
        </div>
    </div>

    <pager v-if="page.next"></pager>
</template>

<script>
    import Pager from './Pager.vue';
    import Post from './Post.vue';

    var _ = require('underscore');
    var URI = require('urijs');

    export default {
        components: {
            Pager,
            Post
        },

        computed: {
            request: function() {
                return {
                    take: 20
                }
            }
        },

        data: function() {
            return {
                page: {
                    current: '',
                    next: '',
                    prev: ''
                },
                pagesLoaded: [],
                posts: []
            }
        },

        methods: {
            getPosts: function(request) {
                var that = this;

                if(this.page.next) {
                    request.page = this.page.next;
                }

                if(this.$parent.user) {
                    request.user_id = this.$parent.user.id;
                }

                if(this.$route.params.tag) {
                    request.tag = this.$route.params.tag;
                }

                request.where = this.$route.where;

                this.$http.get('posts', request).then(function(response) {
                    that.posts = that.posts.concat(response.data.posts.data);

                    that.setPages(response);
                });
            },

            setCurrentPage: function(response) {
                if(response.data.posts.current_page) {
                    this.page.current = response.data.posts.current_page;
                }
            },

            setNextPostsPage: function(response) {
                if(response.data.posts.next_page_url) {
                    var nextPageURL = new URI(response.data.posts.next_page_url);
                    var nextPageQuery = nextPageURL.query(true);
                    this.page.next = nextPageQuery['page'];
                } else {
                    this.page.next = '';
                }
            },

            setPages: function(response) {
                this.setCurrentPage(response);
                this.setNextPostsPage(response);
                this.setPostsPagesLoaded(response);
            },

            setPostsPagesLoaded: function(response) {
                this.pagesLoaded.push(response.data.posts.current_page);
            },

            showPager: function() {
                if(this.page.next !== '') {
                    if(_.contains(this.pagesLoaded, this.page.next)) {
                        return false;
                    } else {
                        return true;
                    }
                }
            },
        },

        ready: function() {
        },

        route: {
            data: function(transition) {
                return Promise.all([
                    this.getPosts(this.request)
                ]);
            }
        }
    }
</script>
