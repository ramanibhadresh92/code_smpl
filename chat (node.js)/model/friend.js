var mongoose = require('mongoose');
var Schema = mongoose.Schema;

// create a schema
var friendSchema = new Schema({
    from_id: String,
    to_id: String,
    status: Number,
	action_user_id: String
}, { collection: 'friend' });

var Friend = mongoose.model('Friend', friendSchema);

module.exports = Friend;