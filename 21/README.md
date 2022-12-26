You need to run these two replacements in PHP Storm to transform the original input file into the Prolog terms file. 
    
    ([a-z]{4}): (\d+)
    $1($2).

    ([a-z]{4}): ([a-z]{4}) ([+\-*/]) ([a-z]{4})
    $1(X) :- $2(A), $4(B), X is A $3 B.

Save it as input.pl.\
Install swipl (I've used homebrew).\
Run swipl, load the file and just ask for an answer.

    volganian@volganian-XL4C2W 21 % swipl
    Welcome to SWI-Prolog (threaded, 64 bits, version 9.0.3)
    SWI-Prolog comes with ABSOLUTELY NO WARRANTY. This is free software.
    Please run ?- license. for legal details.
    
    For online help and background, visit https://www.swi-prolog.org
    For built-in help, use ?- help(Topic). or ?- apropos(Word).
    
    ?- [input].
    true.
    
    ?- root(X).
    X = 309248622142100.