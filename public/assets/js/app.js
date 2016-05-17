/*!
 * (c) 2016, Raphael Marco
 */

(function () {
    'use strict';

    var getUrlParams = function getQueryVars() {
        var params = {};
        var i = location.href.indexOf('?');

        if (i == -1) {
            return params;
        }

        var queryString = location.href.slice(i + 1);

        if (queryString.length == 0) {
            return params;
        }

        queryString = queryString.split('&');

        for (var i = 0; i < queryString.length; i++) {
            var param = queryString[i].split('=');

            params[param[0]] = param[1];
        };

        return params;
    }

    var getQueryVar = function getQueryVar(key) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
       
        for (var i=0; i < vars.length ; i++) {
            var pair = vars[i].split("=");

            if (pair[0] == key) {
                return pair[1];
            }
       }
       
       return null;
    };

    var stringifyUrlParams = function stringifyUrlParams(params) {
        var queryString = '';

        for (var i in params) {
            queryString += i + '=' + params[i] + '&';
        }

        return queryString.substring(0, queryString.length - 1);
    }

    var appData = {
        isNavActive: false,
        disableAction: false,
        paginationPage: 1,
        activeTab: 0,
        
        modals: {
            confirm: false,
            helpBox: false
        }
    };

    var appMethods = {
        toggleNav: function () {
            this.isNavActive = !this.isNavActive;
        },

        showLoginBox: function() {
            var _self = this;
            this.isHelpBoxShowed = false;

            setTimeout(function() {
                _self.isLoginBoxShowed = true;
            }, 500);
        },

        showHelpBox: function() {
            var _self = this;
            this.isLoginBoxShowed = false;

            setTimeout(function() {
                _self.isHelpBoxShowed = true;
            }, 500);
        },

        changePaginationPage: function() {
            var params = getUrlParams();
            params.page = this.paginationPage;

            location = location.pathname + '?' + stringifyUrlParams(params);
        },

        activateTab: function(index) {
            this.activeTab = index;
        },

        printDocument: function() {
            window.print();
        }
    };

    Vue.component('modal', {
        template: '#modal-template',
        props: {
            show: {
                type: Boolean,
                required: true,
                twoWay: true    
            }
        }
    })

    var app = new Vue({
        el: '#app',
        data: appData,
        methods: appMethods
    });

    var currentPage = getQueryVar('page');
    var tabSwitcher = document.querySelector('[data-activated-tab]');
    var mediumEditable = document.querySelector('.medium-editable');
    var modelMasks = document.querySelectorAll('.modal-mask');

    if (currentPage) {
        app.$set('paginationPage', currentPage);
    }

    if (tabSwitcher) {
        app.activateTab(tabSwitcher.getAttribute('data-activated-tab'));
    }

    if (mediumEditable) {
        new MediumEditor(mediumEditable, {
            imageDragging: false,
            toolbar: {
                buttons: [
                    'h1',
                    'h2',
                    'bold',
                    'italic',
                    'underline',
                    'justifyLeft',
                    'justifyCenter',
                    'justifyRight',
                    'justifyFull',
                    'orderedlist',
                    'unorderedlist',
                    'indent',
                    'outdent',
                    'removeFormat'
                ]
            }
        });
    }

    window.onload = function() {
        for (var i = 0; i < modelMasks.length; i++) {
            modelMasks[i].className = modelMasks[i].className.replace('modal-mask-hidden', '');
        };
    };
}());
