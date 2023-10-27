    var mongoose = require('mongoose');
    var Schema = mongoose.Schema;

    // create a schema
    var MessagesSchema = new Schema({
        reply: String,
        type: String,
        category: String,
        from_id: String,
        to_id: String,
        con_id: Number,
        is_read: {type: Number, default: 1},
        from_id_read: {type: Number, default: 0},
        to_id_read: {type: Number, default: 0},
        from_id_del: {type: Number, default: 0},
        to_id_del: {type: Number, default: 0},
        to_id_flush: {type: Number, default: 0},
        from_id_flush: {type: Number, default: 0},
    	created_at: {
            type: Date,
            default: Date.now
        }
    });

    var Messages = mongoose.model('Messages', MessagesSchema);

    module.exports = Messages;