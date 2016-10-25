var _idleSecondsCounter = 0;
var sec_60 = 0;
var total = 0;

function reset(){
    /*$('#timeout-modal').modal('hide');*/
    _idleSecondsCounter = 0;
    sec_60 = 0;
    total = 0;
}    
    
function checkIdle(){ 
    /*var IDLE_TIMEOUT = 30;*/ //seconds
    _idleSecondsCounter = 0;
    sec_60 = 0;
    total = 0;

    
    document.onclick = function() 
    {
        /*$('#timeout-modal').modal('hide');*/
    	_idleSecondsCounter = 0;
        sec_60 = 0;
        total = 0;
    };

    document.onmousemove = function() 
    {
        _idleSecondsCounter = 0;
        sec_60 = 0;
        total = 0;
    };

    document.onkeypress = function() 
    {
        _idleSecondsCounter = 0;
        sec_60 = 0;
        total = 0;
    };

    document.ontouchstart = function() 
    {
        _idleSecondsCounter = 0;
        sec_60 = 0;
        total = 0;
    };

    document.ontouchmove = function() 
    {
        _idleSecondsCounter = 0;
        sec_60 = 0;
        total = 0;
    };

    window.setInterval(CheckIdleTime, 1000);

    function CheckIdleTime() 
    {
        _idleSecondsCounter++;
        total = _idleSecondsCounter % 60;
        /*var oPanel = document.getElementById("time-passed");
        if (oPanel){
        	if(total == 0)
        	{
        		sec_60 = sec_60 + 1;
        	}
        	if(total < 60)
        	{
        		if(total < 10)
        		{
        			oPanel.innerHTML = sec_60 + ":0" + total + "";
        		}
        		else
        		{
        			oPanel.innerHTML = sec_60 + ":" + total + "";
        		}
        	}
        }
    	if (_idleSecondsCounter >= IDLE_TIMEOUT) {
    	    $("#timeout-modal").modal({
    			backdrop: 'static',
    		    keyboard: true
    		});
    	}*/

        if (_idleSecondsCounter >= (60 * 5)) {
            window.location.href = "logout";
        }
    }
}