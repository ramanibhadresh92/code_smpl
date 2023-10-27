var mongoose = require('mongoose');
var Schema = mongoose.Schema;

// create a schema
var userSchema = new Schema({
    fb_id: String,
    username: String,
    fname: String,
    lname: String,
    fullname: String,
    password: String,
    con_password: String,
    pwd_changed_date: String,
    email: String,
    alternate_email: String,
    photo: String,
    thumbnail: String,
    cover_photo: String,
    birth_date: String,
    gender: String,
    created_date: String,
    updated_date: String,
    created_at: String,
    updated_at: String,
    status: String,
    phone: String,
    isd_code: String,
    country: String,
    country_code: String,
    city: String,
    captcha: String,
    member_type: String,
    last_login_time: String,
    forgotcode: String,
    forgotpassstatus: String,
    lat: String,
    long: String,
    login_from_ip: String
}, { collection: 'user' });

var User = mongoose.model('User', userSchema);

module.exports = User;