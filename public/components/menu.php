<!-- Menu -->

<style type="text/css">
	.menu {
		position: absolute;
		z-index: 3;
		bottom: 0;
		width: 100%;
		display: flex;
		flex-direction: column;
		justify-content: flex-end;
		align-items: center;
		flex-wrap: nowrap;
		-webkit-transform: translate(0, 400px);
		transition: transform 0.3s ease-in-out;
	}

	.menu--open {
		-webkit-transform: translate(0, 0);
	}

	.menu > div {
		box-shadow: 0 0 10px rgba(0,0,0,0.25);
	}

	.menu .menuwrapper {
		width: 90%;
		border-radius: 15px;
		border: 0.5px solid rgba(0,0,0, 0.3);
	}

	.menuwrapper div {
		height: 50px;
		color: var(--accent);
		background-color: rgba(255,255,255,0.95);
		display: flex;
		justify-content: center;
		align-items: center;
		border-bottom: 0.5px solid rgba(0,0,0, 0.1);
	}

	.menuwrapper div a {
		height: 100%;
		width: 100%;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.menuwrapper div:hover {
		cursor: pointer;
	}

	.menuwrapper div:last-child {
		border: none;
		border-bottom-left-radius: 15px;
		border-bottom-right-radius: 15px;
	}

	.cancel {
		margin-top: 10px;
		margin-bottom: 10px;
		width: 90%;
		border-radius: 15px;
		border: 0.5px solid rgba(0,0,0, 0.3);;
		height: 50px;
		color: var(--accent);
		background-color: rgba(255,255,255,0.5);
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.cancel:hover {
		cursor: pointer;
	}

	.logout {
		border: none;
		border-top-left-radius: 15px;
		border-top-right-radius: 15px;
		color: red !important;
		font-family: var(--Medium);
	}
</style>

<div id="Menu" class="menu" style="visibility: hidden">
	<div class="menuwrapper">
		<div class="logout"><a href="logout.php">Logout</a></div>			
		<div> <a href="settings.php">Settings</a></div>
		<div> <a href="news.php">News</a></div>			
	</div>
	<div id="Cancel" class="cancel">Cancel</div>		
</div>

<script type="text/javascript">
document.getElementById('Menutrigger').addEventListener('click', function() {
	window.scrollTo(0,0); 
	document.getElementById('Menu').style.visibility = 'visible';
	document.getElementById('Menu').classList.toggle('menu--open');
});
document.getElementById('Cancel').addEventListener('click', function() {
	window.scrollTo(0,0); 
	document.getElementById('Menu').classList.toggle('menu--open');
});

function whichTransitionEvent(){
    var t;
    var el = document.createElement('fakeelement');
    var transitions = {
      'transition':'transitionend',
      'OTransition':'oTransitionEnd',
      'MozTransition':'transitionend',
      'WebkitTransition':'webkitTransitionEnd'
    }

    for(t in transitions){
        if( el.style[t] !== undefined ){
            return transitions[t];
        }
    }
}

var transitionEnd = whichTransitionEvent();
document.getElementById('Menu').addEventListener(transitionEnd, hide, false);

function hide() {
	window.scrollTo(0,0); 
	if (document.getElementById('Menu').style.visibility == 'visible' && !(document.getElementById('Menu').classList.contains('menu--open'))) {
		document.getElementById('Menu').style.visibility = 'hidden';
	} else {
		document.getElementById('Menu').style.visibility = 'visible';
	}
}
</script>