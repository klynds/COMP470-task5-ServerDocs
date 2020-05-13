var http = require('http');
var mysql = require('mysql2');
const StringDecoder = require('string_decoder').StringDecoder;

// mysql -h database-4.ce134bcuydb6.ca-central-1.rds.amazonaws.com -P 3306 -u admin -p
http.createServer(function (req, res) {

    console.log('Request received: ');
    // Set CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Request-Method', '*'); // wildcard is bad for security
    res.setHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST');
    res.setHeader("Access-Control-Allow-Headers", "Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    res.writeHead(200, { 'Content-Type': 'text/plain' });
    console.log('Header Written: ');

    const decoder = new StringDecoder('utf-8'); // pure Node.js solution for parsing json from POST
    var payload = '';
    var objJSON = '';
    let method = req.method;

    console.log(method);
    req.on('data', (data) => {
        payload += decoder.write(data);
    });
    req.on('end', () => {
        payload += decoder.end();
        objJSON = JSON.parse(payload);
        console.log(objJSON.requesttype);

        switch(objJSON.requesttype){
            case "login":
                authAccount(objJSON.user, objJSON.pass).then(r => {
                    res.end(r.toString()); // res.end('callback(\'{\"auth\": \"'+r+'\"}\')');
                });
                break;
            case "create":
                makeAccount(objJSON.user, objJSON.pass, objJSON.mail).then(r => {
                    console.log(r);
                    res.end(JSON.stringify(r));
                    console.log("here");
                });
                break;
            case "getxml":
                getXML(objJSON.lessonid, objJSON.emltype).then(r => {
                    console.log(r);
                    res.end(JSON.stringify(r));
                    console.log("here");
                });
                break;
        }
    });
}).listen(8082);
console.log('Server running on port 8082');

async function authAccount(user, pass) {
    try {
        const con = mysql.createConnection({
            "host" : "database-4.ce134bcuydb6.ca-central-1.rds.amazonaws.com", // database-4.clqggp5gd9bf.us-east-1.rds.amazonaws.com
            "port" : "3306",
            "user" : "admin",
            "password" : "comp466password",
            "database" : "tma2"
        });

        var [rows,schema] = await con.promise().query("SELECT COUNT(*) AS authCount FROM users WHERE uname ="+" '"+user+"'"+" AND pass ="+" '"+pass+"'"); // receives the await object
        console.log(rows[0]);
        if (rows[0].authCount == 0){
            console.log("Error: credentials not valid");
            con.end();
            return false;
        }else{
            console.log("username: "+user+" authenticated");
            con.end();
            return true;
        }
    }catch(ex) {
        console.log(ex.toString()); // remove after debugging for security
    }
}

async function makeAccount(user, pass, mail) {
    try {
        const con = mysql.createConnection({
            "host" : "database-4.ce134bcuydb6.ca-central-1.rds.amazonaws.com",
            "port" : "3306",
            "user" : "admin",
            "password" : "comp466password",
            "database" : "tma2"
        });
        var [rows,schema] = await con.promise().query("SELECT COUNT(*) AS namesCount FROM users WHERE uname ="+" '"+user+"'"); // receives the await object
        if (rows[0].namesCount > 0){
            return "username";
        }
        console.log("username: "+user+" not in use, proceeding to insert operation");
        var [rows,schema] = await con.promise().query("SELECT COUNT(*) AS emailsCount FROM users WHERE email ="+" '"+mail+"'"); // receives the await object

        if (rows[0].emailsCount > 0){
            return "email";
        }
        console.log("email: "+mail+" not in use, proceeding to insert operation");
        const insert  = await con.promise().query("INSERT INTO users (uname, pass, email) VALUES (?, ?, ?)",
            [user, pass, mail]);
        await con.promise().commit();
        console.log("insert operation complete");
        return "success";
    }catch(ex) {
        console.log(ex.toString()); // remove after debugging for security
    }
};

async function getXML(lessonid, emltype) {
    try {
        const con = mysql.createConnection({
            "host" : "database-4.ce134bcuydb6.ca-central-1.rds.amazonaws.com",
            "port" : "3306",
            "user" : "admin",
            "password" : "comp466password",
            "database" : "tma2"
        });

        var [rows,schema]  = await con.promise().query("SELECT "+emltype+" FROM lessons WHERE lessonid="+lessonid);
        console.log("select operation complete");
        console.log(rows[0]);
        return rows[0];
    }catch(ex) {
        console.log(ex.toString()); // remove after debugging for security
    }
};