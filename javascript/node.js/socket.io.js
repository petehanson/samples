// Copyright Up and Running

var app = require('http').createServer()
, io = require('socket.io').listen(app) ;

io.set('close timeout', 15);
io.set('heartbeat timeout', 20);
io.set('heartbeat interval', 7);
io.set('polling duration', 7);

app.listen(8080);


function Ars(icode) {
	this.template_instance_id = 0;
	this.icode = icode;
	this.device_sockets = new Array();
	this.device_presenter = undefined;
	this.current_question = '';
	this.questions = {};
	this.current_answers = new Object();
	this.current_question_number = 1;

    // registers an answer client
	this.registerDevice = function(socket) {
		this.device_sockets.push(socket);
		this.push_update_to_device(socket);
	}

    // removes an answer client
	this.unregisterDevice = function(socket) {
		var pos = array_search(socket,this.device_sockets);

		if (pos != undefined) {
			this.device_sockets.splice(pos,1);
			this.update_devices_registered();
		}

		function array_search(val, array) {
			if(typeof(array) === 'array' || typeof(array) === 'object') {
				var rekey;
				for(var indice in array) {
					if(array[indice] == val) {
						rekey = indice;
						break;
					}
				}
				return rekey;
			}
		}		
	}

    // registers the presenter device
	this.registerPresenter = function(socket) {
		this.device_presenter = socket;
	}
	
	this.setTemplateInstanceId = function(id) {
		this.template_instance_id = id;
	}

	this.has_presenter_joined = function() {
		if (this.device_presenter == undefined) {
			return false;
		} else {
			return true;
		}
	}

    // when a trigger happens and we need to let devices know
	this.push_update_to_device = function(socket) {
		if (this.has_presenter_joined()) {
			socket.emit("device_question_update",{
				question: this.current_question, 
				question_number: this.current_question_number,
				question_answers: this.current_answers,
				template_instance_id: this.template_instance_id
				});
		}
	}

    // when a question is moved to the next or previous question
	this.update_question = function(question_obj) {
		//this.current_question = question_number;
		this.current_question = question_obj.qt;
		this.current_answers = question_obj.qa;
		this.current_question_number = question_obj.qn;

		if (this.questions[this.current_question_number] == undefined) {
			this.questions[this.current_question_number] = {
				submissions: new Array()
				};
		}

		//Changed device update system to just push through the broadcast instead of each register socket
		// this could be a problem if a server runs more than 1 survey at a time
		io.sockets.emit("device_question_update",{
			question: this.current_question, 
			question_number: this.current_question_number,
			question_answers: this.current_answers,
			template_instance_id: this.template_instance_id
			});
	}

	this.update_devices = function() {
		for (i=0; i < this.device_sockets.length; i++) {
			var temp_socket  = this.device_sockets[i];	
			this.push_update_to_device(temp_socket);

		}
	}

	this.update_devices_registered = function() {
		try{
			this.device_presenter.emit("presenter_update_devices_registered",{
				count: this.device_sockets.length
				});
		}catch(e){
			console.log('Presenter Missing Exception: ' + e);
		}
	}

	this.record_submission = function(value) {
		this.questions[this.current_question_number].submissions.push(value);
		this.paint_submissions_number();
	}
	this.clear_submissions = function() {
		//Presenter can reset all submissions
		for (v in this.questions){
			v.submissions = new Array();
		}
		this.paint_submissions_number();

	}
	this.paint_submissions_number = function() {
		var data = {
			submission_count: this.questions[this.current_question_number].submissions.length
			}; //console.log(data);
		try{
			this.device_presenter.emit("presenter_update_submission_count",data);
		}catch(e){
			console.log('Presenter Missing Exception: ' + e);  
		}
	}
	this.update_submission = function(old_value,new_value) { 
		console.log("running array_search");
		var pos = array_search(old_value, this.questions[this.current_question_number].submissions); // find index (just one) of old value

		this.questions[this.current_question_number].submissions.splice(pos,1);
		this.questions[this.current_question_number].submissions.push(new_value); // add updated value

		function array_search(val, array) {
			if(typeof(array) === 'array' || typeof(array) === 'object') {
				var rekey;
				for(var indice in array) {
					if(array[indice] == val) {
						rekey = indice;
						break;
					}
				}
				return rekey;
			}
		}
	}
	this.send_all_submissions = function() {
		socket.emit('paint_submission_results',{
			q:this.questions
			});
	}

	
}

var MYAPP = {

	socket_icodes: {},
	registered_icodes: {},

    // gets the appropriate ARS object that relates to a program code
	getArs: function (icode) {
		if (this.registered_icodes[icode] == undefined) {
			this.registered_icodes[icode] = new Ars(icode);
			console.log('regen icode object in getArs method');
		}
		console.log(this.registered_icodes);
		return this.registered_icodes[icode];
	},

	registerSocketICode: function (socket,icode) {
		this.socket_icodes[socket] = icode;		
	},

	getICodeFromSocket: function (socket) {
		return this.socket_icodes[socket];
	}
}


// socket callbacks

io.sockets.on('connection', function (socket) {
	socket.on('disconnect',function() {
		var icode = MYAPP.getICodeFromSocket(socket);
		var ars = MYAPP.getArs(icode);

		ars.unregisterDevice(socket);
		console.log("Disconnecting " + socket.id + " from " + icode);

	});

	socket.on('device_register',function(msg,register_confirm) {
		var icode = msg.icode;	

		console.log("Registering " + socket.id + " for " + icode);
		MYAPP.registerSocketICode(socket, icode);

		var ars = MYAPP.getArs(icode);
		ars.registerDevice(socket);

		ars.update_devices_registered();

		register_confirm();

	});

	socket.on('device_send_vote',function(msg) {
		var icode = msg.icode;
		var is_edit = parseInt(msg.is_edit); // 1 or 0, flags if this is an overwrite, a re-answer, on a particular quesiton already asked [DonC]
		var ars = MYAPP.getArs(icode);
		if (!is_edit) { //don't update if this is an edit of a previous answer
			ars.record_submission(msg.value);
		} else {
            ars.update_submission(msg.old_value,msg.value);
		}

	});

	socket.on('question_change', function(msg) {
		var question_text = msg.question;
		var icode = msg.icode;
		var q_answers = msg.question_answers;
		var q_number = msg.question_number;
		var q_obj = {
			qt: question_text, 
			qa: q_answers, 
			qn: q_number
		};

		var ars = MYAPP.getArs(icode);
		ars.update_question(q_obj);
		ars.update_devices_registered();
		ars.paint_submissions_number();
	});

	socket.on('presenter_register', function(msg) {
		var icode = msg.icode;
		var template_instance_id = msg.template_instance_id;

		console.log("registering presenter " + socket.id + " for icode: " + icode);
		console.log("presenter template instance ID: " + template_instance_id);

		var ars = MYAPP.getArs(icode);
		ars.registerPresenter(socket);
		ars.setTemplateInstanceId(template_instance_id)
	});
	
	socket.on('retrieve_all_submissions', function(msg) {
		var ars = MYAPP.getArs(msg.icode);
		ars.send_all_submissions();
	});
  
	socket.on('logmyapp', function(){
		console.log(MYAPP.registered_icodes); 
	});

	socket.on('survey_reset', function(msg){
		console.log('presenter sent reset');
		MYAPP.registered_icodes = {};
		console.log(MYAPP);
		if (msg.reset_clients == 1) {
			io.sockets.emit('survey_reset_clients',{show_prompt: true});
		}
		socket.emit('presenter_reset');
	});
	
	socket.on('survey_reset_clients_only', function(msg) {
		console.log("client reset only");
		MYAPP.registered_icodes = {};
		io.sockets.emit('survey_reset_clients',{show_prompt: false});
	});
	
	socket.on('survey_run_start',function(msg) {
		var icode = msg.icode;
		console.log('new ARS survey run for icode ' + icode);
	});
});
