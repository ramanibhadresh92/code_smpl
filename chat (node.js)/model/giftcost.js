var mongoose = require('mongoose');
var Schema = mongoose.Schema;

// create a schema
var giftcostSchema = new Schema({
    price : String,
    credits_by : String,
	credits_at : Number
}, { collection: 'message_gift' });

var Giftcost = mongoose.model('Giftcost', giftcostSchema);
module.exports = Giftcost;

