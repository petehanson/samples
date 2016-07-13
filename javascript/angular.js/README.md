# Angular.js Samples

Below are a sampling of files from various Angular.js projects. These are partial files or just a small library or 
directive to give an idea of Angular.js capabilities. 

## rest.api.file.upload.js

This is an AngularJS directive that helps handle custom image and video uploads in a Photo Tour application. The 
directive is used for associating a form select control with the file input field. It uses a REST API to handle the 
file upload, and ng-file-upload is used to assist with the uploading work. There is a Node.js service that handles the 
REST API and uploaded file.

About the application: The service is an overlay system for Google Photo Tours, that are accessible from Street View. 
A customer would come in, set up an account and link their photo tour in the application. From there, they could
preview their tour, as it resides on Google. They're provided a series of markup controls, where they can drop in 
points of interest, link in rich media about those, points, trigger media activities, based on zoom or view angle, and
it provides a custom table of contents, for finding different views that are of interest. There's two primary focuses
for it's usage, including individual businesses that have a store tour in place and for use in real estate. 

## canvas.sample.js

This demonstrates the manipulation of a canvas. Part of the tour application where it is used allows for the dynamic 
drawing of regions in the tour that can overlay context. This is a demonstration of how that canvas manipulation will 
take place. It draws dots, lines, and arcs, and connects the dots into a solid Polygon.

About the application: The service is an overlay system for Google Photo Tours, that are accessible from Street View. 
A customer would come in, set up an account and link their photo tour in the application. From there, they could
preview their tour, as it resides on Google. They're provided a series of markup controls, where they can drop in 
points of interest, link in rich media about those, points, trigger media activities, based on zoom or view angle, and
it provides a custom table of contents, for finding different views that are of interest. There's two primary focuses
for it's usage, including individual businesses that have a store tour in place and for use in real estate. 


## virtual.keyboard.js

This example demonstrates some AngularJS directives that deal with rendering a virtual keyboard on the user interface. 
It is used to allow users to set shortcut keys for operations in the application. The keyboard will also show what's 
been mapped so far. Here is an image of the interaction (not the end-state user interface): Virtual Keyboard Screenshot.

About the application: The toolset provides a friendly front-end to generating Elasticsearch queries and manipulation
of an API to assist in the discovery process for court cases.  Documents are indexed and inserted into Elasticsearch. 
From there, portions and elements of the documents can be queries and codified from the front-end. 