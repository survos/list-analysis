try {
    // for Node.js
    var autobahn = require('autobahn');
} catch (e) {
    // for browsers (where AutobahnJS is available globally)
}

var connection = new autobahn.Connection({url: 'ws://127.0.0.1:8080/', realm: 'realm1'});


connection.onopen = function (session) {
    console.log('autobahn connection successful!');

    // 1) subscribe to a topic
    function onevent(args) {
        console.log("Event:", args[0]);
    }
    session.subscribe('com.myapp.hello', onevent);

    session.subscribe('download.rappnew', function(args) {
        $('#download_log').prepend(`<li>${args[0]}</li>`);
        console.log(args);
    });

    // 2) publish an event
    session.publish('com.myapp.hello', ['Hello, world!']);

    // 3) register a procedure for remoting
    function add2(args) {
        return args[0] + args[1];
    }
    // session.register('com.myapp.add2', add2);

    // 4) call a remote procedure
    session.call('com.example.multiply', [2, 6]).then(
        function (res) {
            console.log("Result:", res);
        }
    );
};

console.log('opening autobahn connection ...');
connection.open();

