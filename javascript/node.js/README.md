# Node.js Samples

Below are is a sampling of files from various Node.js projects. These are partial files or a small library or service
call to give an idea of the capabilities in Node.js.

## socket.io.js

This is a Node.js server that handles device registrations for answer and presenter devices in support of an Audience 
Response System (ARS). It uses socket IDs to track registrations and also which program code is being registered for. 
It tracks which question the presenter is currently navigating to, pushing updates to the device clients so they can 
refresh their displays with the new question and its available answers. This uses the sockets.io library from NPM 
(Node.js) to handle the communciation between devices. Answer devices are typically mobile devices (this system is 
often used at bring-your-own-device events) and tablets.

About the application: The tool is used during medical information presentations, where the presenter would pause the 
presentation and pose questions to the audience. The audience could use either their own device (phone, tablet, 
computer), connect to a specific URL, and get an interface where they can provide an answer for the current question 
on the screen. As the presenter changes questions, the clients are notified of the change and given the new question 
answer data to render in their displays. The service collects and presents data back through the presenter connection.

## digium.phone.service.js

This is an application designed to run on the Digium phone V8 platform. This VoIP app watches for outgoing calls, 
uses a long polling system (since the platform doesn't support sockets) to keep a constant connection to a 
centralized service (its registration process), listens for alerts, sends alerts based on pattern matching (not feature 
complete yet), and executes a visual display on the phone when an alert is received.

About the application: The tool was defined for 911 alerts in a closed phone network. The typical use case would be a 
school, where in a classroom, a teacher may call 911. If a call like that goes out, the phone itself would update a 
service that 911 was called and then notices are sent out to predefined phones, like the phones in the office, to 
indicate that someone called 911. It's a way to alert admin personal in case of an emergency, without having to send 
someone to the office.

