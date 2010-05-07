NS = new Class({
    initialize:function(myexec,unique) {
        this.myexec = myexec;
        this.stack = [];
        this.result = null;
        this.next = 'next';
		this.firstRun = true; //Indicates that we are in the compilation phase and DO,STEP and FINISH should do things
        this.Context = new Class ({
            initialize:function() {
                this.step=0;
                this.name='';
                this.steps=[];
                this.scope = {}; //holder for loop scope when pushed
            },
            insertStep: function(step) {
				this.steps.splice(this.step++,0,step); //This is adding it during execution of a step.
            },
            getStep:function() {
                if(this.step >= this.steps.length) {
                    return null;
                }
                return this.steps[this.step++];
            }
        });
        this.currentContext = new this.Context();
        if(unique) {
            window.CONTINUE = this.CONTINUE.bind(this);
            window.DONE = this.DONE.bind(this);
            window.AGAIN = this.AGAIN.bind(this);
            window.DO = this.DO.bind(this);
            window.STEP = this.STEP.bind(this);
            window.EXIT = 0;
        }
        this.timerid;
    },
    ticker:function() {
    /*  Performs the next step in the process.  This routine is entered every clock tick and processes just one step (or even
        just prepares to process the next).  It then remembers where it is, and returns.  This is the fundemental process by which
        we can perform long tasks asynchronously.
    */
        var step;
        var repeat = true; //indicates if the engine should repeat without returning  (for steps that are immediate)
        do {
            if((step = this.currentContext.getStep()) === null) {
                this.currentContext.step = 0; //Force to beginning (DO LOOPs loop until an exit or other request seen
                if((step = this.currentContext.getStep()) === null) {
                    //if we still get a null, then we have a bad level
                    this.throwerror("Badly formed context");            
                }
            }
            switch (step.type) {
            case 'f':
				var stepno = this.currentContext.step;
                repeat = false;
                this.next = 'next'; //default is to go on to next step
				this.firstRun = step.firstRun;  //This flag indicates to DO, STEP and FINISH that they should compile their parameters
                //call it
                step.data(this.result);
				step.firstRun=false;  //Do the first run, so all steps included by the function should now be in place
				
                switch(this.next) {
                case 'continue':
                    this.currentContext.step = stepno-1;
                    break; //we will re-execute the step next time through;
                case 'again':
                    this.currentContext.step = 0;
                    break;
                case 'done':
                    this.poplevel();
                    break;
                case 'next':
					this.currentContext.step = stepno;  //We need to explicitly set this because compilation may have added in new steps to execute
                default:
                    //Nothing
                }
                break;
            case 'do':
                this.stack.push(this.currentContext);
                this.scope = null;  
                this.currentContext = step.data;
                break;
            case 'exit':
                this.poplevel();
                break;
            case 'fail':
            default:
                this.throwerror(step.data)
            }
        } while (repeat);
    },
    poplevel: function() {
        if(this.stack.length > 0) {
            this.currentContext = this.stack.pop();
        } else {
            $clear(this.timerid);
            this.whendone(true,this.result); //signal done
        }
    }.protect(),
    throwerror:function(msg) {
        $clear(this.timerid);
        console.error(msg+" at D:"+this.stack.length+" S:"+this.currentContext.step);
        this.result.error = msg;
        this.whendone(false,this.result);
        throw "NS terminating";  //need to throw in order not to return to calling function
    }.protect(),
    STEP:function(func) {
            var boundfunc;
			if(this.firstRun) {
				switch($type(func)) {
				case 'function': 
					boundfunc = func.bind(this);
					this.currentContext.insertStep({type:'f',data:boundfunc,firstRun:true});
					break;
				case 'array':
					this.DO(func); //recursively add the next level
					break;
				case 'number':
					if (func == EXIT) {
						this.currentContext.insertStep({type:'exit'});
						break;
					}
					//deliberately fall through
				default:
					this.currentContext.insertStep({type:'fail',data:'Invalid Entry in DO array'});
				}
			}
					
        return this;
    },
    DO:function(funcarray) {
        var context;
		if(this.firstRun) { //only if in compile phase.
			this.stack.push(this.currentContext);
			context = new this.Context();
			this.currentContext = context;
			funcarray.each(function(func){
				this.STEP(func);
			}.bind(this));
			this.currentContext = this.stack.pop();
			this.currentContext.insertStep({type:'do',data:context});
		}
        return this;
    },

    CONTINUE:function(result) {
        if($type(result)) this.result = result;
        this.next = 'continue';
		return this;
    },
    AGAIN:function(result) {
        if($type(result)) this.result = result;
        this.next = 'again';
		return this;
    },
    DONE:function(result) {
        if($type(result)) this.result = result;
        this.next = 'done';
		return this;
    },
    EXEC: function(params,complete) {
        var boundexec = this.myexec.bind(this);
        this.whendone = complete;
        boundexec(params); //initialisation (ie done depth 0 initialisation

/*  At this point, we have looked at depth 0 (and possibly greater) and created all the steps. so
    Now we have to reset the steps and get the ticker to start executing them
*/
        this.currentContext.step = 0;
        this.timerid = this.ticker.periodical(1,this);
    }
}); 


