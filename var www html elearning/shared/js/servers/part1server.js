/*
admin:comp466password
database-2.clqggp5gd9bf.us-east-1.rds.amazonaws.com
3306

$ mysql -h database-2.clqggp5gd9bf.us-east-1.rds.amazonaws.com -P 3306 -u admin -p

You have tried to call .then(), .catch(), or invoked await on the result of query that is not a promise, which is a programming error. Try calling con.promise().query(), or require('mysql2/promise') instead of 'mysql2' for a promise-compatible version of the query interface. To learn how to use async/await or Promises check out documentation at https://www.npmjs.com/package/mysql2#using-promise-wrapper, or the mysql2 documentation at https://github.com/sidorares/node-mysql2/tree/master/documentation/Promise-Wrapper.md
Error: You have tried to call .then(), .catch(), or invoked await on the result of query that is not a promise, which is a programming error. Try calling con.promise().query(), or require('mysql2/promise') instead of 'mysql2' for a promise-compatible version of the query interface. To learn how to use async/await or Promises check out documentation at https://www.npmjs.com/package/mysql2#using-promise-wrapper, or the mysql2 documentation at https://github.com/sidorares/node-mysql2/tree/master/documentation/Promise-Wrapper.md

https://github.com/hnasr/javascript_playground/blob/master/mysql-javascript/index.js

fuser -n tcp -k 8087

Access to XMLHttpRequest at 'http://127.0.0.1:8088/' from origin 'http://localhost:63342' has been blocked by CORS policy:
Request header field content-type is not allowed by Access-Control-Allow-Headers in preflight response.

 */

var http = require('http');
var mysql = require('mysql2');
const StringDecoder = require('string_decoder').StringDecoder;

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
                });
                break;
            case "newbookmark":
                makeBookmark(objJSON.user, objJSON.url, objJSON.title, objJSON.category).then(r => {
                    retrieveBookmark(objJSON.user).then(r => {
                        res.end(JSON.stringify(r));
                    });
                });
                break;
            case "getbookmark":
                retrieveBookmark(objJSON.user).then(r => {
                    res.end(JSON.stringify(r));
                });
                break;
            case "updatebookmark":
                updateBookmark(objJSON.bookmarkid, objJSON.user, objJSON.url, objJSON.title, objJSON.category).then(r => {
                    retrieveBookmark(objJSON.user).then(r => {
                        res.end(JSON.stringify(r));
                    });
                });
                break;
            case "removebookmark":
                removeBookmark(objJSON.bookmarkid).then(r => {
                    retrieveBookmark(objJSON.user).then(r => {
                        res.end(JSON.stringify(r));
                    });
                });
                break;
            case "init":
                retrievePopular().then(r => {
                    res.end(JSON.stringify(r));
                });
                break;
        }
    });
}).listen(8090);
console.log('Server running on port 8090');

async function authAccount(user, pass) {
    try {
        const con = mysql.createConnection({
            "host" : "database-4.ce134bcuydb6.ca-central-1.rds.amazonaws.com",
            "port" : "3306",
            "user" : "admin",
            "password" : "comp466password",
            "database" : "part1"
        });

        var [rows,schema] = await con.promise().query("SELECT COUNT(*) AS authCount FROM users WHERE uname ="+" '"+user+"'"+" AND pw ="+" '"+pass+"'"); // receives the await object
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

async function removeBookmark(bid) {
    try {
        const con = mysql.createConnection({
            "host" : "database-4.ce134bcuydb6.ca-central-1.rds.amazonaws.com",
            "port" : "3306",
            "user" : "admin",
            "password" : "comp466password",
            "database" : "part1"
        });
        const update  = await con.promise().query("DELETE FROM bookmarks WHERE bookmarkid = ?",
            [bid]);
        await con.promise().commit();
        console.log("delete operation complete");
        return "Operation success";
    }catch(ex) {
        console.log(ex.toString()); // remove after debugging for security
    }
};

async function retrievePopular(){
    try {
        const con = mysql.createConnection({
            "host" : "database-4.ce134bcuydb6.ca-central-1.rds.amazonaws.com",
            "port" : "3306",
            "user" : "admin",
            "password" : "comp466password",
            "database" : "part1",
            "multipleStatements" : "true"
        });
        var [rows,schema] = await con.promise().query("SELECT url, count(*) FROM bookmarks GROUP BY url ORDER BY count(*) DESC LIMIT 10"); // receives the await object
        console.log(rows);
        await con.promise().commit(); // NEW
        con.end();
        return rows;
    }catch(ex) {
        console.log(ex.toString()); // remove after debugging for security
    }
};

async function retrieveBookmark(user){
    try {
        const con = mysql.createConnection({
            "host" : "database-4.ce134bcuydb6.ca-central-1.rds.amazonaws.com",
            "port" : "3306",
            "user" : "admin",
            "password" : "comp466password",
            "database" : "part1",
            "multipleStatements" : "true"
        });
        var [rows,schema] = await con.promise().query("SELECT url, count(*) FROM bookmarks GROUP BY url ORDER BY count(*) DESC LIMIT 10"); // receives the await object
        console.log(rows);
        await con.promise().commit(); // NEW
        con.end();
        return rows;
    }catch(ex) {
        console.log(ex.toString()); // remove after debugging for security
    }
};

async function updateBookmark(bid, user, url, title, category) {
    try {
        const con = mysql.createConnection({
            "host" : "database-4.ce134bcuydb6.ca-central-1.rds.amazonaws.com",
            "port" : "3306",
            "user" : "admin",
            "password" : "comp466password",
            "database" : "part1"
        });
        const update  = await con.promise().query("UPDATE bookmarks SET uname = ?, url = ?, title = ?, category = ? WHERE bookmarkid = ?",
            [user, url, title, category, bid]);
        await con.promise().commit();
        console.log("update operation complete");
        return "Operation success";
    }catch(ex) {
        console.log(ex.toString()); // remove after debugging for security
    }
};

async function makeBookmark(user, url, title, category) {
    try {
        const con = mysql.createConnection({
            "host" : "database-4.ce134bcuydb6.ca-central-1.rds.amazonaws.com",
            "port" : "3306",
            "user" : "admin",
            "password" : "comp466password",
            "database" : "part1"
        });
        const insert  = await con.promise().query("INSERT INTO bookmarks (uname, url, title, category) VALUES (?, ?, ?, ?)",
            [user, url, title, category]);
        await con.promise().commit();
        console.log("insert operation complete");
        return "Operation success";
    }catch(ex) {
        console.log(ex.toString()); // remove after debugging for security
    }
};

async function makeAccount(uname, pw, email) { // USED
    try {
        const con = mysql.createConnection({
            "host" : "database-4.ce134bcuydb6.ca-central-1.rds.amazonaws.com",
            "port" : "3306",
            "user" : "admin",
            "password" : "comp466password",
            "database" : "part1"
        });
        var [rows,schema] = await con.promise().query("SELECT COUNT(*) AS namesCount FROM users WHERE uname ="+" '"+uname+"'"); // receives the await object
        if (rows[0].namesCount > 0){
            return "username";
        }
        console.log("username: "+uname+" not in use, proceeding to insert operation");
        var [rows,schema] = await con.promise().query("SELECT COUNT(*) AS emailsCount FROM users WHERE email ="+" '"+email+"'"); // receives the await object

        if (rows[0].emailsCount > 0){
            return "email";
        }
        console.log("email: "+email+" not in use, proceeding to insert operation");
        const insert  = await con.promise().query("INSERT INTO users (uname, pw, email) VALUES (?, ?, ?)",
            [uname, pw, email]);
        await con.promise().commit();
        console.log("insert operation complete");
        return "success";
    }catch(ex) {
        console.log(ex.toString()); // remove after debugging for security
    }
}