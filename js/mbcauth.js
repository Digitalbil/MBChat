/*
 	Copyright (c) 2010 Alan Chandler
    This file is part of MBChat.

    MBChat is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    MBChat is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with MBChat (file COPYING.txt).  If not, see <http://www.gnu.org/licenses/>.
*/
function MBCAuth(internal,purgeInterval) {
        var confirmedServer = false;
        var pi = purgeInterval;
        if(Browser.ie7) {
            $('rsa_generator').removeClass('loading');
            $('rsa_generator').removeClass('hide');  //just in case
            $('rsa_generator').set('html','<span class="error">Internet Explorer V7 is not supported.  Chat will work with Internet Explorer 6 and v8 and later as well as Firefox, Chrome, Safari and Opera.</span>');
            return;
        }
             
        function confirmTimeout() {
            if(!confirmedServer) {
                $('rsa_generator').removeClass('loading');
                $('rsa_generator').removeClass('hide');  //just in case
                $('rsa_generator').set('html','<span class="error">Security Alert, Server NOT confirmed.  Please notify security</span>');
            }
        }
        var loginError = function(usernameError) { 
            $('rsa_generator').addClass('hide');
            $('authblock').removeClass('hide');
            $('login_error').removeClass('hide');
            if(usernameError) {
                $($('login').username).addClass('error');
                $($('login').password).addClass('error');
            } else {
                $($('login').password).addClass('error');
            }
        }

        var loginReq = new Request.JSON({
            url:'login/index.php',
            link:'chain',
            onComplete:function(response,t) {
            if(response && response.status) {
                if(response.trial) { //responding with the returned security key
                    if(response.trial == checkNo) {
                        //matched
                        confirmedServer = true;
                        coordinator.done('verify',{});
                        if (internal) {
                            $('rsa_generator').addClass('hide');
                            $('authblock').removeClass('hide');
                                // and wait for user to respond
                        }
                    } else {
                        confirmTimeout();
                        confirmedServer = true; //not really, but stops timeout from trying to do same thing
                    }
                } else if (response.login && confirmedServer) {
                    loginRequestOptions = response.login;
                    coordinator.done('login',{});
                }
            } else { 
                if(internal) {
                    loginError(response.usererror);
                }
            }
            }
        });

        window.addEvent('domready',function () {
            $('login').addEvent('submit', function(e) {
                e.stop();
                var auth = {};
                auth.U = $('login').username.value;
                auth.P = $('login').password.value;
                if(auth.U.contains('$')) {
                    loginError(false);
                    return ;
                }
                if(auth.P == '') {
                    if(!guestsAllowed) {
                        loginError(false);
                        return;
                    }
                    auth.P = 'guest';
                    auth.U = '$$G'+auth.U;
                }
 
                $('rsa_generator').removeClass('hide');
                $('authblock').addClass('hide');
                $('login_error').addClass('hide');
                $($('login').username).removeClass('error');
                $($('login').password).removeClass('error');
                loginReq.post({user:auth.U,pass:hex_md5(auth.P),purge:pi}); //only get here when doing own authentication so hex_md5 will be available
            });
            // This initial request will see if the server is the correct one - ie has the correct private key to encrypt this encCheckNo
            loginReq.post({user:'$$$',pass:remoteKey,trial:encCheckNo});
            confirmTimeout.delay(10000); //give server 10 seconds to come back with correct response.
            coordinator.done('dom',{});
        });
};

