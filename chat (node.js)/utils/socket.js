var helper = require('../utils/MessageBlock'),
    socketidbox = {},   
    users = [],
    _ = require('lodash'),
    method = socket.prototype,
    arrayKey = [],
    newUserCreate = [],
    getFriendList = [],
    fs = require('fs');

function socket(io, logger, conversationPath) {
    /**  Socket event starts */ 
    io.on('connection', function(socket) {
        socket.on('userInfo', function(userinfo) {
           // Add this user into Users Groups.....
            var socketIds= [];
            userinfo.status= 'online';
            if (users.length == 0) {
               // socketidbox[userinfo.id] = socket.id;
                socketIds.push(socket.id);
                userinfo.socketIds= socketIds;
                users.push(userinfo);
            } else {
                var is_exists = _.findIndex(users, { 'id': userinfo.id });
                if(is_exists <0) {
                    socketIds.push(socket.id);
                    userinfo.socketIds= socketIds;
                    users.push(userinfo);
                } else {
                    socketIds = users[is_exists].socketIds;
                    socketIds.push(socket.id);
                    users[is_exists].socketIds= socketIds;
                }
            } 

            // Get Friend List......
            helper.getFriendList(userinfo.id, function(friends) {
                var friends =  _.uniq(friends);
                getFriendListBox = [];
                if (friends.length != 0) {
                    var loop = 0;
                    var last = friends.length;
                    var secondLast = last - 1;

                    for (i = 0; i < last; i++) { 
                        var currentId = friends[i];
                        var current = _.filter(users, { 'id': currentId });

                        if(current.length === 0) {
                            var data = {
                                id: currentId,
                                status: 'offline'
                            };
                            getFriendListBox.push(data);
                        } else {
                            var data = _.get(current, 0);
                            getFriendListBox.push(data);
                        }

                        if(i == secondLast) {     
                            var selfSocketId = _.findIndex(users, {'id': userinfo.id});
                            if(selfSocketId >=0) {
                                users[selfSocketId].socketIds.forEach(function(ele, index, array) {
                                    io.to(ele).emit('getSelfUserList', getFriendListBox);
                                });
                            }

                            var online = _.filter(getFriendListBox, {'status': 'online'});
                            var away = _.filter(getFriendListBox, {'status': 'away'});
                            
                            // Send TO ALl Friends
                            var sendAll = { online: userinfo.id};
                            var mergeUsers = _.union(online, away);
                            mergeUsers.forEach(function(element, index, array) {
                                element.socketIds.forEach(function(ele, index, array) {
                                    io.to(ele).emit('getAnotherUserList', sendAll);
                                });
                            }); 
                        }   
                    }              
                } else {
                    var selfSocketId = _.findIndex(users, {'id': userinfo.id});
                    if(selfSocketId >=0) {
                        users[selfSocketId].socketIds.forEach(function(ele, index, array) {
                            io.to(ele).emit('getSelfUserList', getFriendListBox);
                        }); 
                    }
                }
            });
        });
          
        socket.on('setAway', function(id) {
            if(id) {
                //update user information
                var usersUpdateKey = _.findIndex(users, {'id': id});
                if(usersUpdateKey >= 0) { 
                      users[usersUpdateKey].status = 'away';  
                }
                helper.getFriendList(id, function(friends) {
                    var friends =  _.uniq(friends);
                    if (friends) {
                        _(friends).forEach(function(value) {
                            var indexOfUsers = _.findIndex(users, {'id': value});
                            if(indexOfUsers >= 0) {
                                var indexOfUsers = _.filter(users, {'id': value});
                                var sendAll = { away: id};
                                indexOfUsers.forEach(function(element, index, array) {
                                    element.socketIds.forEach(function(ele, index, array) {
                                        io.to(ele).emit('getAnotherUserList', sendAll);
                                    });
                                });  
                            } 
                        });
                    }
                });
            }
        }); 

        socket.on('setOnline', function(id) {
            if(id) {
                //update user information
                var usersUpdateKey = _.findIndex(users, {'id': id});
                if(usersUpdateKey >= 0) { 
                    users[usersUpdateKey].status = 'online';  
                }
                helper.getFriendList(id, function(friends) {
                    var friends =  _.uniq(friends);
                    if (friends) {
                        _(friends).forEach(function(value) {
                            var indexOfUsers = _.findIndex(users, {'id': value});
                            if(indexOfUsers >= 0) {
                                var indexOfUsers = _.filter(users, {'id': value});
                                var sendAll = { online: id};
                                indexOfUsers.forEach(function(element, index, array) {
                                    element.socketIds.forEach(function(ele, index, array) {
                                        io.to(ele).emit('getAnotherUserList', sendAll);
                                    });
                                }); 
                            } 
                        });
                    }
                });
            }
        }); 


        socket.on('callBuddies', function(uid) { 
            if(uid) {
                // Get Friend List......
                helper.getFriendList(uid, function(friends) {
                    var friends =  _.uniq(friends);
                    getFriendListBox = [];
                    if (friends.length != 0) {
                        var loop = 0;
                        var last = friends.length;
                        var secondLast = last - 1;

                        for (i = 0; i < last; i++) { 
                            var currentId = friends[i];
                            var current = _.filter(users, { 'id': currentId });

                            if(current.length === 0) {
                                var data = {
                                    id: currentId,
                                    status: 'offline'
                                };
                                getFriendListBox.push(data);
                            } else {
                                var data = _.get(current, 0);
                                getFriendListBox.push(data);
                            }

                            if(i == secondLast) {     
                                var selfSocketId = _.findIndex(users, {'id': uid});
                                if(selfSocketId >=0) {
                                    users[selfSocketId].socketIds.forEach(function(ele, index, array) {
                                        io.to(ele).emit('getSelfUserList', getFriendListBox);
                                    });
                                }

                               /* var online = _.filter(getFriendListBox, {'status': 'online'});
                                var away = _.filter(getFriendListBox, {'status': 'away'});
                                
                                // Send TO ALl Friends
                                var sendAll = { online: uid};
                                var mergeUsers = _.union(online, away);
                                mergeUsers.forEach(function(element, index, array) {
                                    element.socketIds.forEach(function(ele, index, array) {
                                        io.to(ele).emit('getAnotherUserList', sendAll);
                                    });
                                }); */
                            }   
                        }              
                    } else {
                        var selfSocketId = _.findIndex(users, {'id': uid});
                        if(selfSocketId >=0) {
                            users[selfSocketId].socketIds.forEach(function(ele, index, array) {
                                io.to(ele).emit('getSelfUserList', getFriendListBox);
                            }); 
                        }
                    }
                });
            }
        });

        socket.on('callOnlineUsers', function(id) { 
            if(id) {
                helper.getFriendList(id, function(friends) {
                    var friends =  _.uniq(friends);
                    if (friends) {
                        var onlineusers = [];
                        var friends = JSON.parse(JSON.stringify(friends));
                        if(friends.length >0) {
                            var friendsLength = friends.length;
                            var int = 1;
                            _(friends).forEach(function(value) {
                                var indexOfUsers = _.findIndex(users, {'id': value, 'status': 'online'});
                                if(indexOfUsers >= 0) {
                                    var current = {'id' : value,  'status' : 'online'};
                                    onlineusers.push(current);
                                }
                               
                                if(int == friendsLength) {
                                    var currentusers = _.filter(users, {'id': id});
                                    currentusers.forEach(function(element, index, array) {
                                        element.socketIds.forEach(function(ele, index, array) {
                                            io.to(ele).emit('getOnlineUsers', onlineusers);
                                        });
                                    });
                                }
                                int++;
                            });
                        } else {
                            var currentusers = _.filter(users, {'id': id});
                            currentusers.forEach(function(element, index, array) {
                                element.socketIds.forEach(function(ele, index, array) {
                                    io.to(ele).emit('getOnlineUsers', onlineusers);
                                });
                            });
                        }
                    }
                });
            }
        });

        socket.on('callOnlineUsersids', function(id) { 
            if(id) {
                helper.getFriendList(id, function(friends) {
                    var friends =  _.uniq(friends);
                    if (friends) {
                        var onlineusers = [];
                        var friends = JSON.parse(JSON.stringify(friends));
                        if(friends.length >0) {
                            var friendsLength = friends.length;
                            var int = 1;
                            _(friends).forEach(function(value) {
                                var indexOfUsers = _.findIndex(users, {'id': value, 'status': 'online'});
                                if(indexOfUsers >= 0) {
                                    //var current = {'id' : value,  'status' : 'online'};
                                    onlineusers.push(value);
                                }
                               
                                if(int == friendsLength) {
                                    var currentusers = _.filter(users, {'id': id});
                                    currentusers.forEach(function(element, index, array) {
                                        element.socketIds.forEach(function(ele, index, array) {
                                            io.to(ele).emit('callOnlineUsersids', onlineusers);
                                        });
                                    });
                                }
                                int++;
                            });
                        } else {
                            var currentusers = _.filter(users, {'id': id});
                            currentusers.forEach(function(element, index, array) {
                                element.socketIds.forEach(function(ele, index, array) {
                                    io.to(ele).emit('callOnlineUsersids', onlineusers);
                                });
                            });
                        }
                    }
                });
            }
        }); 
        
        socket.on('getOnlineUsersquniq', function(id,wall_user_id,baseUrl) { 
            if(id) {
                helper.getFriendList(id, function(friends) {
                    var friends =  _.uniq(friends);
                    if (friends) {
                        var onlineusers = [];
                        var friends = JSON.parse(JSON.stringify(friends));
                        if(friends.length >0) {
                            var friendsLength = friends.length;
                            var int = 1;
                            _(friends).forEach(function(value) {
                                var indexOfUsers = _.findIndex(users, {'id': value, 'status': 'online'});
                                if(indexOfUsers >= 0) {
                                    var current = {'id' : value,  'status' : 'online'};
                                    onlineusers.push(current);
                                }
                               
                                if(int == friendsLength) {
                                    var currentusers = _.filter(users, {'id': id});
                                    currentusers.forEach(function(element, index, array) {
                                        element.socketIds.forEach(function(ele, index, array) {
                                            io.to(ele).emit('getOnlineUsersquniq', friends,wall_user_id,baseUrl);
                                        });
                                    });
                                }
                                int++;
                            });
                        } else {
                            var currentusers = _.filter(users, {'id': id});
                            currentusers.forEach(function(element, index, array) {
                                element.socketIds.forEach(function(ele, index, array) {
                                    io.to(ele).emit('getOnlineUsersquniq', onlineusers,wall_user_id,baseUrl);
                                });
                            });
                        }
                    }
                });
            }
        });

        socket.on('disconnect', function() { 
            var spliceId = "";
            for (var i = 0; i < users.length; i++) {
                var inI = users[i].socketIds.indexOf(socket.id);
                if(inI != -1) {
                    users[i].socketIds.splice(inI, 1);
                    if(users[i].socketIds.length == 0) {
                        users.splice(i, 1);
                    }
                    io.emit('exit', users[i]);
                }
            }
        });
 
        socket.on('callRecentMessagesUserList', function(data) {
            var id = data.id;
            var category = data.category; 
            if(id) {
                helper.getRecentMessagesUserList(id, category, function(history) {
                    //  Get Socket Id From User List

                    var history = JSON.parse(JSON.stringify(history));
                    
                    var historyLength = history.length;
                    var newhistory = [];
                    var int = 0;
                    if(historyLength) {
                        _(history).forEach(function(value) {
                            var statusid = value.to_id;
                            if(statusid == id) {
                                statusid = value.from_id;
                            }

                            var statusrecord = _.filter(users, {'id': statusid});
                            if(statusrecord.length) {
                                var status = statusrecord[0].status;
                                value['status'] = status;
                                newhistory.push(value);
                            } else {
                                var status = 'offline';
                                value['status'] = status;
                                newhistory.push(value);
                            }
                            if(int == historyLength - 1) {
                                var socketInfo = _.filter(users, {'id': id, 'status': 'online'});
                                    socketInfo[0].socketIds.forEach(function(ele, index, array) {
                                    io.to(ele).emit('getRecentMessagesUserList', newhistory, category);
                                });       
                            }
                            int++;
                        });
                    } else {
                        var socketInfo = _.filter(users, {'id': id, 'status': 'online'});
                        socketInfo[0].socketIds.forEach(function(ele, index, array) {
                            io.to(ele).emit('getRecentMessagesUserList', newhistory, category);
                        });                        
                    }


                });
            }
        });

        socket.on('callHistoryForMessageWall', function(ids, category, start, limit) {
            //logger.debug('Show History' + JSON.stringify(data));
            if (!limit) {
                limit = 5;
            }
            if (!start) {
                start = 0;
            }
            helper.getHistory(ids, category, start, limit, function(conversation) {
                var sendToSocketIds = _.findIndex(users, {'id': sendTo});
                if(sendToSocketIds >=0) {
                    users[sendToSocketIds].socketIds.forEach(function(ele, index, array) {
                        io.to(ele).emit('getHistoryForMessageWall', conversation);
                    }); 
                }
            });
        }); 

        socket.on('sendMessage', function(data) { 
            /** Calling saveMsgs to save messages into DB */
            helper.saveMessage(data, function(result) { 
                if(!(_.isEmpty(result))) {
                    var category = result.category;
                    // Send To Self
                    if(category == 'page') {
                        var from_id = result.pageSELF;
                    } else {
                        var from_id = result.from_id;
                    }

                    var indexKey = _.findIndex(users, {'id': from_id});

                    if(indexKey>=0) {
                        users[indexKey].socketIds.forEach(function(ele, index, array) {
                            io.to(ele).emit('sendMessageToSelf', result);
                        });
                    }

                    // Send To Others
                    if(category == 'page') {
                        var to_id = result.pageOTHER;
                    } else {
                        var to_id = result.to_id;
                    }
                    var indexKey = _.findIndex(users, {'id': to_id});
                    
                    if(indexKey>=0) {
                        users[indexKey].socketIds.forEach(function(ele, index, array) {
                            io.to(ele).emit('sendMessageToOther', result);
                        }); 
                    } 
                }
            });
        });

        socket.on('sendMessagewithgift', function(data) {
            if(!(_.isEmpty(data))) {
                var toIds = data.to_id;
               /** Calling saveMsgs to save messages into DB */
                helper.saveMessageex(data, function(result) {
                    if(!(_.isUndefined(result[0]))) {
                        var tempResult = result[0];
                        var from_id = tempResult.from_id;
                        var indexKey = _.findIndex(users, {'id': from_id});
                        if(indexKey>=0) {
                            tempResult.to_id = toIds;
                            users[indexKey].socketIds.forEach(function(ele, index, array) {
                                io.to(ele).emit('sendMessageToSelf', tempResult);
                            });
                            
                            _.forEach(result, function(v) {
                                var ids = v.to_id;
                                _.forEach(ids, function(id) {
                                    var indexKey = _.findIndex(users, {'id': id});
                                    if(indexKey>=0) {
                                        users[indexKey].socketIds.forEach(function(ele, index, array) {
                                            io.to(ele).emit('sendMessageToOther', v);
                                        });
                                    }
                                });     
                            });
                        }
                    }
                });
            }
        });

        socket.on('callHistoryForChatWall', function(data, sendTo, start, limit) {
            //logger.debug('Show History' + JSON.stringify(data));
            if (!limit) {
                limit = 5;
            }
            if (!start) {
                start = 0;
            }
            helper.getHistory(data, start, limit, function(conversation) {
                var sendToSocketIds = _.findIndex(users, {'id': sendTo});
                if(sendToSocketIds >=0) {
                    users[sendToSocketIds].socketIds.forEach(function(ele, index, array) {
                        io.to(ele).emit('getHistoryForChatWall', conversation);
                    });
                }
            });
        }); 

        socket.on('userImage', function(data) {
            //path to store uploaded files (NOTE: presumed you have created the folders)
            var currentDate = new Date();
            name = currentDate.getTime() + data.fileName;
            var buffer = data.base64image;
            var fileName = conversationPath + 'messages/' + data.fileName;
            var matches = buffer.match(/^data:([A-Za-z-+\/]+);base64,(.+)$/)
            base64Data = new Buffer(matches[2], 'base64');
            
            fs.open(fileName, 'a', 0755, function(err, fd) {
                if (err) throw err;
                fs.writeFile(fd, base64Data, function(err) {
                    if (err) throw err;
                    fs.close(fd, function() {
                        /* Add message in database */
                        if(data.category == 'page') {
                            var filterMsgData = {
                                reply: 'messages/'+data.fileName,
                                type: "image",
                                from_id: data.from_id,
                                to_id: data.to_id,
                                category: data.category,
                                pageOTHER: data.pageOTHER,
                                pageSELF: data.pageSELF
                            };
                        } else {
                            var filterMsgData = {
                                reply: 'messages/'+data.fileName,
                                type: "image",
                                from_id: data.from_id,
                                to_id: data.to_id,
                                category: data.category
                            };
                        }

                        helper.saveMessage(filterMsgData, function(result) { 
                            if(!(_.isEmpty(result))) {
                                // Send To Self

                                var category = result.category;

                                if(category == 'page') {
                                    var from_id = result.pageSELF;
                                } else {
                                    var from_id = result.from_id;
                                }

                                var indexKey = _.findIndex(users, {'id': from_id});
                                if(indexKey>=0) {
                                    users[indexKey].socketIds.forEach(function(ele, index, array) {
                                        io.to(ele).emit('sendMessageToSelf', result);
                                    });
                                }

                                // Send To Others
                                if(category == 'page') {
                                    var to_id = result.pageOTHER;
                                } else {
                                    var to_id = result.to_id;
                                }
                                var indexKey = _.findIndex(users, {'id': to_id});
                                if(indexKey>=0) {
                                    users[indexKey].socketIds.forEach(function(ele, index, array) {
                                        io.to(ele).emit('sendMessageToOther', result);
                                    });
                                }
                            }
                        });
                    });
                });
            });
        });

        socket.on('searchUserStatus', function(selfId, data) {
            var $data = JSON.parse(JSON.stringify(data));
            var $dataLength = $data.length;
            var $itration = $dataLength - 1;
            for (var i = 0; i < $dataLength; i++) {
                (function(i) {
                    var id = $data[i].id;
                    var indexKey = _.findIndex(users, {'id': id});
                    if(indexKey>=0) {
                        var status = users[indexKey].status;
                        $data[i].status = status;
                    } else {
                        $data[i].status = 'offline';   
                    }
                    if($itration == i) {
                        var selKeys = _.findIndex(users, {'id': selfId});
                        if(selKeys>=0) {
                            users[selKeys].socketIds.forEach(function(ele, index, array) {
                                io.to(ele).emit('chatboxsearchresult', $data);
                            });
                        }
                    }
                })(i);
            }   
        });

        socket.on('fillwithlabel', function(selfId, data) {
            var $data = JSON.parse(JSON.stringify(data));
            var $dataLength = $data.length;
            var $itration = $dataLength - 1;
            for (var i = 0; i < $dataLength; i++) {
                (function(i) {
                    var id = $data[i].id;
                    var indexKey = _.findIndex(users, {'id': id});
                    if(indexKey>=0) {
                        var status = users[indexKey].status;
                        $data[i].status = status;
                    } else {
                        $data[i].status = 'offline';   
                    }
                    if($itration == i) {
                        var selKeys = _.findIndex(users, {'id': selfId});
                        if(selKeys>=0) {
                            users[selKeys].socketIds.forEach(function(ele, index, array) {
                                io.to(ele).emit('returnwithlabel', $data);
                            });
                        }
                    }
                })(i);
            }   
        });

        socket.on('recentuserlistfillwithlabel', function(selfId, data, isStop) {
            var $data = JSON.parse(JSON.stringify(data));
            var $dataLength = $data.length;
            var $itration = $dataLength - 1;
            for (var i = 0; i < $dataLength; i++) {
                (function(i) {
                    var id = $data[i].user_id;
                    var indexKey = _.findIndex(users, {'id': id});
                    if(indexKey>=0) {
                        var status = users[indexKey].status;
                        $data[i].status = status;
                    } else {
                        $data[i].status = 'offline';   
                    }
                    if($itration == i) {
                        var selKeys = _.findIndex(users, {'id': selfId});
                        if(selKeys>=0) {
                            users[selKeys].socketIds.forEach(function(ele, index, array) {
                                io.to(ele).emit('recentuserlistfillwithlabel', $data, isStop);
                            });
                        }
                    }
                })(i);
            }   
        });

        socket.on('getChatUsersForRecentStatus', function(data, selfId) {
            var $data = JSON.parse(JSON.stringify(data));
            var $dataLength = $data.length;
            var $itration = $dataLength - 1;
            for (var i = 0; i < $dataLength; i++) {
                (function(i) {
                    var id = $data[i].id;
                    var indexKey = _.findIndex(users, {'id': id});
                    if(indexKey>=0) {
                        var status = users[indexKey].status;
                        $data[i].status = status;
                    } else {
                        $data[i].status = 'offline';   
                    }
                    if($itration == i) {
                        var selKeys = _.findIndex(users, {'id': selfId});
                        if(selKeys>=0) {
                            users[selKeys].socketIds.forEach(function(ele, index, array) {
                                io.to(ele).emit('getChatUsersForRecentStatus', $data);
                            });
                        }
                    }
                })(i);
            }   
        });

        socket.on('getBasicInfoOfUser', function(id, userID) {
            if(userID) { 
                var result = {id: id};
                var data = _.filter(users, {'id': id});
                if(data.length) {
                    var status = data[0].status;
                    result.status = status;
                } else { 
                    result.status = 'offline';
                }

                var userdata = _.findIndex(users, {'id': userID});
                if(userdata >=0) {
                    users[userdata].socketIds.forEach(function(ele, index, array) {
                        io.to(ele).emit('setBasicInfoOfUser', result);
                    });
                }
            }
        });


        socket.on('getuserlabel', function(id, uid, codekey) {
            if(uid) { 
                var result = {id: id, codekey: codekey};
                var data = _.filter(users, {'id': id});
                if(data.length) {
                    var status = data[0].status;
                    result.status = status;
                } else { 
                    result.status = 'offline';
                }

                var userdata = _.findIndex(users, {'id': uid});
                if(userdata >=0) {
                    users[userdata].socketIds.forEach(function(ele, index, array) {
                        io.to(ele).emit('getuserlabel', result);
                    });
                }
            }
        });

        socket.on('getBasicInfoOfUserDJSUHSFS', function(id, userID) {
            if(userID) { 
                var result = {id: id};
                var data = _.filter(users, {'id': id});
                if(data.length) {
                    var status = data[0].status;
                    result.status = status;
                } else { 
                    result.status = 'offline';
                }

                var userdata = _.findIndex(users, {'id': userID});
                if(userdata >=0) {
                    users[userdata].socketIds.forEach(function(ele, index, array) {
                        io.to(ele).emit('getBasicInfoOfUserDJSUHSFS', result);
                    });
                }
            }
        });

        /*socket.on('domuteout', function(id, userID) {
            if(userID) { 
                var result = {id: id};
                var data = _.filter(users, {'id': id});
                if(data.length) {
                    var status = data[0].status;
                    result.status = status;
                } else { 
                    result.status = 'offline';
                }

                var userdata = _.findIndex(users, {'id': userID});
                if(userdata >=0) {
                    users[userdata].socketIds.forEach(function(ele, index, array) {
                        io.to(ele).emit('domuteout', result);
                    });
                }
            }
        }); 

        socket.on('doblockout', function(id, userID) {
            if(userID) { 
                var result = {id: id};
                var data = _.filter(users, {'id': id});
                if(data.length) {
                    var status = data[0].status;
                    result.status = status;
                } else { 
                    result.status = 'offline';
                }

                var userdata = _.findIndex(users, {'id': userID});
                if(userdata >=0) {
                    users[userdata].socketIds.forEach(function(ele, index, array) {
                        io.to(ele).emit('doblockout', result);
                    });
                }
            }
        }); */

        socket.on('allwhoisaroundusers', function() {
            $filterUsers = _.map(users, 'id');
            io.emit('allwhoisaroundusers', $filterUsers);
        }); 

        socket.on('friendswhoisaround', function(uid) {
            /*if(uid) {
                // Get Friend List......
                helper.getFriendList(uid, function(friends) {
                    getFriendListBox = [];
                    if (friends.length != 0) {
                        var loop = 0;
                        var last = friends.length;
                        var secondLast = last - 1;

                        for (i = 0; i < last; i++) { 
                            var currentId = friends[i];
                            var current = _.filter(users, { 'id': currentId });

                            if(current.length === 0) {
                            } else {
                                getFriendListBox.push(currentId);
                            }

                            if(i == secondLast) {     
                                var selfSocketId = _.findIndex(users, {'id': uid});
                                if(selfSocketId >=0) {
                                    users[selfSocketId].socketIds.forEach(function(ele, index, array) {
                                        io.to(ele).emit('friendswhoisaround', getFriendListBox);
                                    });
                                }
                            }   
                        }              
                    } else {
                        var selfSocketId = _.findIndex(users, {'id': uid});
                        if(selfSocketId >=0) {
                            users[selfSocketId].socketIds.forEach(function(ele, index, array) {
                                io.to(ele).emit('friendswhoisaround', getFriendListBox);
                            }); 
                        }
                    }
                });
            }*/
            $filterUsers = _.map(users, 'id');
            io.emit('friendswhoisaround', $filterUsers);
        }); 

        socket.on('whoisaroundsearch', function($data) {
            $filterUsers = _.map(users, 'id');
            $data['onlineIdsList'] = $filterUsers;
            io.emit('whoisaroundsearch', $data);
        }); 
        
        socket.on('allwhoisaroundusersuniq', function() {
            $filterUsers = _.map(users, 'id');
            io.emit('allwhoisaroundusersuniq', $filterUsers);
        });
    });
}

module.exports = socket;