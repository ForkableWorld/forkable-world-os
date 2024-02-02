/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
require('./bootstrap');
const vueEnabled = true;
if(vueEnabled){
    /**
     * Next, we will create a fresh Vue application instance and attach it to
     * the page. Then, you may begin adding components to this application
     * or customize the JavaScript scaffolding to fit your unique needs.
     */
    window.Vue = require('vue');
    /**
     * The following block of code may be used to automatically register your
     * Vue components. It will recursively scan this directory for the Vue
     * components and automatically register them with their "basename".
     *
     * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
     */

    const files = require.context('./', true, /\.vue$/i)
    files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key)))
	Vue.component('jobs', require('./components/JobsComponent.vue'));
//Vue.component('example-component', require('./components/ExampleComponent.vue'));
    Vue.component(
        'passport-clients',
        require('./components/passport/Clients.vue').default
    );

    Vue.component(
        'passport-authorized-clients',
        require('./components/passport/AuthorizedClients.vue').default
    );

    Vue.component(
        'passport-personal-access-tokens',
        require('./components/passport/PersonalAccessTokens.vue').default
    );
    const app = new Vue({
        el: '#app'
    });
}

if(!window._){
    window._ = require('lodash');
}
if(!window.$){
    window.$ = window.jQuery = require('jquery');
}

if(!window.jQuery){
    window.$ = window.jQuery = require('jquery');
}

var qm = {
    api: {
        patch: function (url, data) {
            // Default options are marked with *
            return fetch(url, {
                method: 'PATCH', // *GET, POST, PUT, DELETE, etc.
                //mode: 'cors', // no-cors, *cors, same-origin
                //cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
                //credentials: 'same-origin', // include, *same-origin, omit
                headers: {
                    'Content-Type': 'application/json',
                    // 'Content-Type': 'application/x-www-form-urlencoded',
                },
                //redirect: 'follow', // manual, *follow, error
                //referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
                body: JSON.stringify(data) // body data type must match "Content-Type" header
            }).catch(error => {
                qm.popup.showErrorMessage(error)
            });
        }
    },
    datatable: {
        reload: function(){
            try {
                window.LaravelDataTables["dataTableBuilder"].ajax.reload();
            }catch (e) {
                console.error(e);
            }
        }
    },
    popup: {
        pusherEventToToast: function(e, eventName){
            console.info("Pusher Event Received: "+eventName, e);
            qm.popup.showToast(e.title, e.confirmButtonText, e.icon, e.clickHandler || e.url)
        },
        showToast: function(title, confirmButtonText, icon, clickHandler){
            function isString(x) {return Object.prototype.toString.call(x) === "[object String]"}
            if(isString(clickHandler)){
                var url = clickHandler;
                clickHandler = function () {
                    window.location.href = url;
                }
            }
            var Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: true,
                confirmButtonText: confirmButtonText,
                timer: 15000,
                timerProgressBar: true,
                onOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                    toast.addEventListener('onClick', clickHandler)
                }
            })
            Toast.fire({
                icon: icon || 'success',
                title: title
            }).then((result) => {
                if (result.value) {
                    clickHandler();
                }
            })
        },
        showRefreshToast: function(title){
            qm.popup.showToast(title, "Refresh", 'success', function () {window.location.reload();})
        },
        showErrorMessage: function(error){
            debugger
            try {
                Swal.showValidationMessage(error)
            } catch (e) {
                console.error(e)
            }
            throw new Error(error)
        },
        confirmAndDeleteValue: function(url, field, currentValue, name){
            //debugger
            var titleCaseField = qm.stringHelper.titleCase(field);
            var title = 'Are you sure you want to permanently delete the '+titleCaseField;
            if(name){title += " for "+name;}
            Swal.fire({
                title: title+"?",
                text: "Current Value: "+currentValue,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.value) {
                    var data = {};
                    data[field] = null;
                    Pace.restart();
                    qm.popup.showToast("One moment while I delete that and then you can refresh...",
                        "Refresh", 'warning', function () {window.location.reload();})
                    return qm.api.patch(url, data)
                        .then(response => {
                            if (!response.ok) {qm.popup.showErrorMessage(response.statusText)}
                            qm.popup.showRefreshToast(name+" "+titleCaseField+' Deleted!')
                            qm.datatable.reload();
                            return response.json()
                        })
                        .catch(error => {
                            qm.popup.showErrorMessage(error)
                        })
                }
            })
        },
        examples: {
            github: function (url, data) {
                Swal.fire({
                    title: 'Submit your Github username',
                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Look up',
                    showLoaderOnConfirm: true,
                    preConfirm: (login) => {
                        return fetch(`//api.github.com/users/${login}`)
                            .then(response => {
                                if (!response.ok) {qm.popup.showErrorMessage(response.statusText);}
                                return response.json()
                            })
                            .catch(error => {
                                qm.popup.showErrorMessage(error)
                            })
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.value) {
                        Swal.fire({
                            title: `${result.value.login}'s avatar`,
                            imageUrl: result.value.avatar_url
                        })
                    }
                })
            },
        },
        putText: function (url, field, value) {
            var titleCaseField = qm.stringHelper.titleCase(field);
            Swal.fire({
                title: 'Modify '+titleCaseField,
                input: 'text',
                inputValue: value,
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Save',
                showLoaderOnConfirm: true,
                preConfirm: (value) => {
                    var data = {};
                    data[field] = value;
                    return qm.api.patch(url, data)
                        .then(response => {
                            if (!response.ok) {qm.popup.showErrorMessage(response.statusText);}
                            qm.popup.showRefreshToast(name+" "+titleCaseField+' Updated!')
                            return response.json()
                        })
                        .catch(error => {
                            qm.popup.showErrorMessage(error)
                        })
                },
                allowOutsideClick: true
            })
        },
        putValues: function (url, data) {
            Swal.fire({
                title: 'Edit',
                input: 'text',
                inputValue: value,
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Look up',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return qm.api.patch(url, data)
                        .then(response => {
                            if (!response.ok) {qm.popup.showErrorMessage(response.statusText);}
                            return response.json()
                        })
                        .catch(error => {
                            qm.popup.showErrorMessage(error)
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.value) {
                    Swal.fire({
                        title: `${result.value.login}'s avatar`,
                        imageUrl: result.value.avatar_url
                    })
                }
            })
        },
        deleteValue: function (url, field) {
            Swal.fire({
                title: 'Delete',
                input: 'text',
                inputValue: value,
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Look up',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return qm.api.patch(url, data)
                        .then(response => {
                            if (!response.ok) {qm.popup.showErrorMessage(response.statusText);}
                            return response.json()
                        })
                        .catch(error => {
                            qm.popup.showErrorMessage(error)
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.value) {
                    Swal.fire({
                        title: `${result.value.login}'s avatar`,
                        imageUrl: result.value.avatar_url
                    })
                }
            })
        }
    },
    stringHelper: {
        titleCase: function (string) {
            string = string.replace(/_/g, " ");
            var sentence = string.toLowerCase().split(" ");
            for(var i = 0; i< sentence.length; i++){
                sentence[i] = sentence[i][0].toUpperCase() + sentence[i].slice(1);
            }
            sentence = sentence.join(" ");
            return sentence;
        }
    },
}

var notifications = [];
const NOTIFICATION_TYPES = {
    follow: 'App\\Notifications\\UserFollowed',
    newPost: 'App\\Notifications\\NewPost'
};
$(document).ready(function() {
    // check if there's a logged in user
    if(window.Laravel && window.Laravel.userId) {
        $.get('/api/v6/notifications', function (response) {
            addNotifications(response.data, "#notifications");
        });
        window.Echo.private(`App.User.${Laravel.userId}`)
            .listen('.App\\Events\\PrivateEvent', (e) => {
                qm.popup.pusherEventToToast(e, '.App\\Events\\PrivateEvent');
            })
            // .listen('.App\\Events\\AnalysisCompleted', (e) => {
            //     // WORKS!  Channel: private-App.User.230, Event: App\Events\AnalysisCompleted
            //     // For some reason we need a dot and NO DOT from pusher
            //     alert(".App\\Events\\AnalysisCompleted!"+ JSON.stringify(e));
            // })
            // .listen('App\\Events\\AnalysisCompleted', (e) => {
            //     // DOES NOT WORK FOR SOME REASON
            //     alert("App\\Events\\AnalysisCompleted!"+ JSON.stringify(e));
            // })
            .listen('AnalysisCompleted', (e) => {
                // WORKS!  Channel: private-App.User.230, Event: App\Events\AnalysisCompleted
                // For some reason we need a full namespace from pusher and no namespace in listener
                qm.popup.pusherEventToToast(e, 'AnalysisCompleted');
            })
            // .listen('analysis.completed', (e) => {
            //     // DOES NOT WORK FOR SOME REASON
            //     alert("analysis.completed!"+ JSON.stringify(e));
            // })
            .listen('.App\\Notifications\\NewPost', (e) => {
                qm.popup.pusherEventToToast(e, '.App\\Notifications\\NewPost');
            })
            .listen('.App\\Notifications\\UserFollowed', (e) => {
                qm.popup.pusherEventToToast(e, '.App\\Notifications\\UserFollowed');
            })
            .notification((notification) => {
                console.info("Got pusher notification!", notification);
                qm.popup.pusherEventToToast(notification, 'notification');
                addNotifications([notification], '#notifications');
            });
    } else{
        console.warn("no Laravel.userId! Laravel is: ", window.Laravel)
    }
});
function addNotifications(newNotifications, target) {
    notifications = _.concat(notifications, newNotifications);
    // show only last 5 notifications
    //notifications.slice(0, 5);
    showNotifications(notifications, target);
}
function showNotifications(notifications, target) {
    console.log("showing notifications for target "+target, notifications);
    $(target + '-count').html(notifications.length);
    if(notifications.length) {
        var htmlElements = notifications.map(function (notification) {
            return makeNotification(notification);
        });
        $(target + '-menu').html(htmlElements.join(''));
        $(target).addClass('has-notifications')
    } else {
        $(target + '-menu').html('<li class="dropdown-header">No notifications</li>');
        $(target).removeClass('has-notifications');
    }
}
// Make a single notification string
function makeNotification(notification) {
    var to = routeNotification(notification);
    var notificationText = makeNotificationText(notification);
    return '<li><a href="' + to + '">' + notificationText + '</a></li>';
}
// get the notification route based on it's type
function routeNotification(notification) {
    var to = `?read=${notification.id}`;
    if(notification.type === NOTIFICATION_TYPES.follow) {
        to = 'users' + to;
    } else if(notification.type === NOTIFICATION_TYPES.newPost) {
        const postId = notification.data.post_id;
        to = `posts/${postId}` + to;
    }
    return '/' + to;
}
// get the notification text based on it's type
function makeNotificationText(notification) {
    var text = '';
    if(notification.type === NOTIFICATION_TYPES.follow) {
        const name = notification.data.follower_name;
        text += `<strong>${name}</strong> followed you`;
    } else if(notification.type === NOTIFICATION_TYPES.newPost) {
        const name = notification.data.following_name;
        text += `<strong>${name}</strong> published a post`;
    }
    return text;
}


/*

const app = new Vue({
    el: '#app',

    data: {
        messages: [],
        newMessage: '',
        user: '',
        typing: false
    },

    methods: {
        sendMessage() {
            // add new message to messages array
            this.messages.push({
                user: Laravel.user,
                message: this.newMessage
            });

            // clear input field
            this.newMessage = '';

            // persist to database
        },
        isTyping() {
            let channel = Echo.private('chat');

            setTimeout(function() {
                channel.whisper('typing', {
                    user: Laravel.user,
                    typing: true
                });
            }, 300);
        },
        created() {
            let _this = this;

            Echo.private('chat')
                .listenForWhisper('typing', (e) => {
                    this.user = e.user;
                    this.typing = e.typing;

                    // remove is typing indicator after 0.9s
                    setTimeout(function() {
                        _this.typing = false
                    }, 900);
                });
        },
    }
});



if (window.Notification) {
    console.log('Notifications are supported!');
} else {
    alert('Notifications aren\'t supported on your browser! :(');
}
export default {
    data() {
        return {
            newPostTitle: "",
            newPostDesc: ""
        }
    },
    created() {
        this.listenForChanges();
    },
    methods: {
        addPost(postName, postDesc) {
            // check if entries are not empty
            if(!postName || !postDesc)
                return;
            // make API to save post
            axios.post('/api/post', {
                title: postName, description: postDesc
            }).then( response => {
                if(response.data) {
                    this.newPostTitle = this.newPostDesc = "";
                }
            })
        },
        listenForChanges() {
            Echo.channel('posts')
                .listen('PostPublished', post => {
                    if (! ('Notification' in window)) {
                        alert('Web Notification is not supported');
                        return;
                    }
                    Notification.requestPermission( permission => {
                        let notification = new Notification('New post alert!', {
                            body: post.title, // content for the alert
                            icon: "https://pusher.com/static_logos/320x320.png" // optional image url
                        });
                        // link to page on clicking the notification
                        notification.onclick = () => {
                            window.open(window.location.href);
                        };
                    });
                })
        }
    }
}
*/
