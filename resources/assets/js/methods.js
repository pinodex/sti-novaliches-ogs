/*!
 * (c) 2017, Raphael Marco
 */

'use strict'

import axios from 'axios'
import serialize from 'form-serialize'
import Ajax from './ajax.js'

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

export default {

    toggleSideBar: function () {
        this.ui.nav.sideBarActive = !this.ui.nav.sideBarActive
    },

    closeOverlays: function () {

    },

    asyncSubmit: function (element) {
        this.form.disabled = true

        let url = element.target.action,
            data = serialize(element.target, { hash: true })

        axios.post(url, data, { maxRedirects: 0 }).then(response => {
            switch (response.data.status) {
                case Ajax.AUTH_SUCCESS:
                    window.location.reload()
                    
                    break

                case Ajax.AUTH_SUCCESS_REDIRECT:
                    this.$toast.open({
                        message: response.data.message,
                        type: 'is-success',
                        duration: response.data.duration || 3000
                    })

                    if (response.data.fold) {
                        this.form.login.state = false
                    }

                    setTimeout(() => window.location = response.data.data.location, 500)

                    break

                case Ajax.AUTH_FAIL:
                    this.$toast.open({
                        message: response.data.message,
                        type: 'is-danger',
                        duration: 3000
                    })

                    this.form.errors = true
                    this.form.disabled = false

                    setTimeout(() => {
                        this.form.errors = false
                    }, 3000)

                    break
            }
        }).catch(error => {
            this.form.disabled = false
            this.showAsyncErrorMessage()
        })
    },

    showAsyncErrorMessage: function (data, message) {
        message = message || 'An error occurred while communicating with the server'

        this.$snackbar.open({
            message, type: 'is-warning'
        })
    }

}
