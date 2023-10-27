var mongoose = require('mongoose');
var Schema = mongoose.Schema;

// create a schema
var creditsSchema = new Schema({
	user_id : String,
    joined_date : {
        type: Date,
        default: Date.now
    },
    ended_date : {
        type: Date,
        default: Date.now
    },
    credits : Number,
    credits_desc : String,
    status : {type: String, default: '1'},
    detail : String
}, { collection: 'users_credits' });

var Credits = mongoose.model('Credits', creditsSchema);
module.exports = Credits;

