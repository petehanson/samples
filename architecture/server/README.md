# Server Infrastructure
 
## Job Infrastructure

This mockup presents a how we'd define and build out a batch formulation processing system.  The web servers where used
handle interactions and provide a job submission api. The backend stack, used job handlers and work that was loaded into
a queue. Job handlers would pull data from the queue, pull the necessary source files from S3, process, and put the
results back into S3, along with updating their job. 

During the application build, this architecture changed slightly, but for the most part remained how it was designed.