App.Console.init = function(){
    
    App.Refs.CONSOLE_INPUT.bind('keydown', function(evt){
        App.Console.Bash.detectCommand();
    });
}

