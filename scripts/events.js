var updateTimer = function( )
{
	var serverOffset = 0;
	
	//TODO: We need to pull live data to populate this
	//Don't forget, months are zero based!
	//target  = Math.round( Date.UTC(2013, 3 -1, 22, 20, 0, 0, 0) / 1000 );

	var timer = setInterval( RefreshClock, 1000 );
	
	var zeroPad = function(number) { return (number >= 10 ? '' + number : '0'+number); };
	
	var d1 = document.getElementById('d1');
	var d2 = document.getElementById('d2');
	var h1 = document.getElementById('h1');
	var h2 = document.getElementById('h2');
	var m1 = document.getElementById('m1');
	var m2 = document.getElementById('m2');
	var s1 = document.getElementById('s1');
	var s2 = document.getElementById('s2');
	var remainDays;
	var remainHours;
	var remainMinutes;
	
	function RefreshClock( )
	{
		var timeCur = Math.round( new Date().getTime() / 1000 );
		
		if (serverOffset === 0 && typeof serverTime !== 'undefined')
			{ serverOffset = (typeof serverTime !== "undefined"? serverTime : 0); }

		var secsRemaining = target - timeCur;
		if( secsRemaining < 0 )
		{
			//clearInterval( timer );
			//TODO: Let's get some kind of event pro
			//element.innerHTML = 'It\'s happening right now!';
			return;
		}

		var days = zeroPad(Math.floor( secsRemaining / 86400 ));
		if (days != remainDays) {
			remainDays = days;
			d1.innerHTML = remainDays.charAt(0);
			d2.innerHTML = remainDays.charAt(1);
 		}
		var hours = zeroPad(Math.floor( (secsRemaining  % 86400) / 60 / 60));
		if (hours != remainHours) {
			remainHours = hours;
			h1.innerHTML = remainHours.charAt(0);
			h2.innerHTML = remainHours.charAt(1);
 		}
 		var minutes = zeroPad(Math.floor( ( secsRemaining % 3600 ) / 60 ));
		if (minutes != remainMinutes) {
			remainMinutes = minutes;
			m1.innerHTML = remainMinutes.charAt(0);
			m2.innerHTML = remainMinutes.charAt(1);
 		}
 		var remainSeconds = zeroPad(secsRemaining % 60);
		s1.innerHTML = remainSeconds.charAt(0);
		s2.innerHTML = remainSeconds.charAt(1);
	}
};

var init = null;
init = setInterval(( function () {
	var countdownElementReady = false;
	
	return function() {
		try
		{
			if (!countdownElementReady)
			{
				if(document.getElementById('s2'))
				{
					countdownElementReady = true;
				}
			}
			
			if (countdownElementReady)
			{
				//Everything looks good. Let's get stuff happening.
				clearInterval(init);
				updateTimer();
			}
		}
		catch (e)
		{
			//If we end up here, it usually means that the page isn't ready, and so we just want to skip by until it is.
			;
		}
	};
	
	
	})(), 100);
