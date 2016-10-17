# API Designs

This contains samples of API designs that I've done for different projects.

## REST API

This document served as an initial structure for a REST API I started to put together to handle encrypted message
exchange.  It uses public/private key authentication and messaging signing. The service is a clearning house for
message delivery between users. 

The document outlines the types of HTTP verbs and endpoints used, indicating what payload can look like and the 
anticipated responses from the server. This particular API was built in Node.js, using a custom wrapper I put together
that emulates how the context system in AWS Lambda works. This way, I can use AWS Gateway and AWS Lambda to be my
server side host for this information, storing and managing the data in an RDS instance. 