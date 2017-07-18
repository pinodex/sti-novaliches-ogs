/*!
 * (c) 2017, Raphael Marco
 */

'use strict'

import Vue from 'vue'
import Buefy from 'buefy'
import timeago from 'timeago.js'
import qs from 'qs'

import methods from './methods.js'

Vue.use(Buefy, {
    defaultIconPack: 'fa'
})

Vue.directive('focus', {
    inserted: (el) => {
        el.focus()
    }
})

let app = new Vue({
    el: '#app',
    
    data: function () {
        return {
            ui: {
                nav: {
                    sideBarActive: false
                }
            },

            modal: {
                helpModal: false
            },

            form: {
                disabled: false,
                errors: false,

                login: {
                    state: true,
                    id: null
                }
            }
        }
    },

    computed: {

        isSsoLogin: function () {
            return isNaN(this.form.login.id)
        }

    },

    methods
})
