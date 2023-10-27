var MessageBlock = require('../model/MessageBlock'),
    Messages = require('../model/Messages'),
    Session = require('../model/session'),
    Friend = require('../model/friend'),
    Credits = require('../model/credits'),
    Giftcost = require('../model/giftcost'),
    User = require('../model/user'),
    logger = require('../utils/logger'),
    fs = require('fs'),
    _ = require('lodash'), 
    asyncLoop = require('node-async-loop'),
    merge = require('merge'), original, cloned;

var conv = {
    isConversationPresent: function(data, callback) {
        /** Function to check conversation is present in  table */
        var is_present = false;
        var con_id = "";

        MessageBlock.findOne().or([
            { $and: [{ to_id: data.to_id, from_id: data.from_id }] },
            { $and: [{ to_id: data.from_id, from_id: data.to_id }] }
        ]).exec(function(err, result) {
            if (err) throw err;
            //logger.debug("Is conversation present : " + JSON.stringify(result));
            if (result) {
                /* data for callback starts*/
                is_present = true;
                con_id = result.con_id;
            } else {
                //data for callback 
                is_present = false;
                con_id = 0
            }
            callback({
                is_present: is_present,
                con_id: con_id
            });
        });
    },

    uptCreAtConID: function(con_id, callback) {
        MessageBlock.updateOne({'con_id': con_id}, {$currentDate: {'created_at': true}}, function() {
            callback(true);   
        });
    },

    fetchGiftCost: function(callback) {
        Giftcost.findOne({}, {}, { sort: { 'created_at' : -1 } }, function(err, post) {
            if (err) {
            } 
            var post = JSON.parse(JSON.stringify(post));
            if(post.price != undefined || pos.price != null) {
                $cost = post.price;
            } else {
                $cost = 0;
            }

            callback($cost);
        });
    },
   
    oldConversation: function(data, callback) {
        // save the user 
        var newMessages = new Messages({
            reply: data.reply,
            type: data.type,
            from_id: data.from_id,
            to_id: data.to_id,
            con_id: data.con_id,
            category: data.category,
            is_read: 1
        });

        
        newMessages.save(function(err, result) {
            if (err) {
            } 

            if(result.category == 'inbox' && result.type == 'gift') {

                conv.fetchGiftCost(function(cost) {

                    var newCredits = new Credits({
                        user_id: result.from_id,
                        credits: '-'+cost,
                        credits_desc: 'gift',
                        detail: result.to_id
                    });
                    newCredits.save(function(err, data) {
                        if (err) {
                        }

                        callback(result);
                    });
                });
            } else {
                callback(result);
            }
        });
    },

    insertConversation: function(data, callback) {
        /** create a new Messages */
        var newMessageBlock = new MessageBlock({
            from_id: data.from_id,
            to_id: data.to_id,
            con_id: data.con_id
        });

        /** Save conversation reply */
        newMessageBlock.save(function(err, result) {
            if (err) {
            }

            callback();
        });
    },
    
    newConversation: function(data, callback) {
        /** Insert msg in collections **/
        conv.insertConversation(data, function() {
            conv.oldConversation(data, function(result) {
                callback(result);
            });
        });
    },
   
    getLastConversationId: function(callback) {
        MessageBlock.findOne()
            .sort('-con_id')
            .exec(function(err, result) {
                if (result) {
                    var conversationid = result.con_id;
                    conversationid++;
                    callback({
                        ID: conversationid
                    });
                } else {
                    callback({
                        ID: 0
                    });
                }
            });
    },

    saveMessage: function(data, callback) {
        if(!(_.isEmpty(data))) {
            var category = data.category;
        
            var to_id = data.to_id;
            var from_id = data.from_id;
            var checkData = { to_id: to_id, from_id: from_id };
                
            conv.isConversationPresent(checkData, function(isPresent) {
                if (isPresent.is_present) {
                    var con_id = isPresent.con_id;
                    conv.uptCreAtConID(con_id, function(res){
                        data.con_id = con_id;                    
                        conv.oldConversation(data, function(result) {
                            var result = JSON.parse(JSON.stringify(result));
                            if(category == 'page') {
                                result['pageOTHER'] = data.pageOTHER;
                                result['pageSELF'] = data.pageSELF;
                            }
                            callback(result);
                        });
                    });
                } else {
                    conv.getLastConversationId(function(con_id) {
                        data.con_id = con_id.ID;
                        conv.newConversation(data, function(result) {
                            var result = JSON.parse(JSON.stringify(result));
                            if(category == 'page') {
                                result['pageOTHER'] = data.pageOTHER;
                                result['pageSELF'] = data.pageSELF;
                            }
                            callback(result);
                        });
                    });
                }
           });
        }
    },

    saveMessageex: function(data, callback) {
        if(!(_.isEmpty(data))) {
            var ids = data.to_id;
            var from_id = data.from_id;
            var dataOutput = [];
            asyncLoop(ids, function(i, v) { 
                var checkData = { to_id: i, from_id: from_id };
                var currentData = data;
                currentData.to_id = i;
                
                conv.isConversationPresent(checkData, function(isPresent) {
                    if (isPresent.is_present) {
                        var con_id = isPresent.con_id;
                        conv.uptCreAtConID(con_id, function(res){
                            currentData.con_id = con_id;
                            conv.oldConversation(currentData, function(result) {
                                var result = JSON.parse(JSON.stringify(result));
                                dataOutput.push(result);
                                v();
                            });
                        });
                    } else {
                        conv.getLastConversationId(function(con_id) {
                            currentData.con_id = con_id.ID;
                            conv.newConversation(currentData, function(result) { 
                                var result = JSON.parse(JSON.stringify(result));
                                dataOutput.push(result);
                                v();
                            });
                        });
                    }
               });
            }, function (err)
            {
                callback(dataOutput);
            });
        }
    },

    getHistory: function(data, category, start, limit, callback) {
        conv.isConversationPresent(data, function(isPresent) {
            //logger.debug("History conversation id" + JSON.stringify(isPresent) + "start : " + start + "limit :" + limit);
            Messages.find({ con_id: isPresent.con_id })
                .sort('-created_at')
                .skip(start)
                .limit(limit)
                .exec(function(err, result) {
                    if (err) throw err;
                    callback(result);
                });
        });
    },
    
    getBasicInfoOfUser: function(data, callback) {
        var id = data.id;
        if(id) {
            User.findOne({'_id' : id}, function(err, user) {
                if (err) throw err;
                if(!(_.isEmpty(user))) {
                    data.fullname = user.fullname;
                    data.city = user.city;
                    data.country = user.country;
                    data.status = true;
                    callback(data);
                }
            });
        }
    },
    
    getRecentMessagesUserList: function(id, category, callback) {
        MessageBlock.find({ $or: [{ to_id: id}, {from_id: id }] }).distinct('con_id').exec(function(err, result) {
            if (err) throw err;
            storedResult = [];
            var result = JSON.parse(JSON.stringify(result));
            var resultLength = result.length;
            if (resultLength >0) {
                var itration = resultLength - 1;
                var k=0;
                for (var i = 0; i < resultLength; i++) {
                    (function(i) {
                        Messages.findOne( { $and : [{ con_id: result[i] }, { category: category }, { to_id_del: 0}, { to_id_flush: 0}] }).sort({created_at: -1}).exec(function(err, data) {
                        if (err) throw err;
                        var data = JSON.parse(JSON.stringify(data));
                        if(data) {
                            storedResult.push(data);
                            if(itration == i) {
                                callback(storedResult);     
                            }
                        } else {
                           callback(storedResult);
                        }
                    });
                    })(i);
                }   
            } else {
                callback(storedResult);
            }
        });
    },

    getFriendList: function(id, callback) {
        storedResult = [];
        Friend.find([{ $or: [{ $and: [{ to_id: id, status: '1' }] }, { $and: [{ from_id: id, status : '1' }] } ] }]).exec(function(err, result) {
        //Friend.find({ to_id: id}).exec(function(err, result) {    
        if (err) throw err;
            if (result) {
                var result = JSON.parse(JSON.stringify(result));
                var resultLength = result.length;
                var itration = 0;

                if(resultLength>0) {
                    // helper function
                    var check = function() {
                        if (resultLength == itration) {
                            callback(storedResult);
                        }
                    }

                    for (var i = 0; i < resultLength; i++) {
                        (function(i) {
                            if(result[i].from_id == id) {
                                storedResult[i] = result[i].to_id;
                            } else {
                                storedResult[i] = result[i].from_id;
                            }
                            itration++; 
                            check();
                        })(i);  
                    }
                } else {
                    callback(storedResult);    
                }
            } else {
                callback(storedResult);
            }
        });
    },  
}

module.exports = conv;