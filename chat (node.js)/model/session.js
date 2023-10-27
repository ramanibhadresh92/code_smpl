var mongoose = require('mongoose');
var Schema = mongoose.Schema;

// create a schema
var SessionSchema = new Schema({
    id: String,
    data: String
});

var Session = mongoose.model('Session', SessionSchema);

module.exports = Session;
