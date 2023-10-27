var mongoose = require('mongoose');
var Schema = mongoose.Schema;

// create a schema
var messageblock = new Schema({
    from_id: String,
    to_id: String,
    from_id: String,
    con_id: Number,
    created_at: {
        type: Date,
        default: Date.now
    }
});

var Messageblock = mongoose.model('Messageblock', messageblock);

module.exports = Messageblock;