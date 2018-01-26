var Utils =
    {
    // STRINGS

    trim: function(myString)
        {
        if (!myString) myString = "";

        while (myString.substring(0,1)==" ") myString = myString.substring(1, myString.length);
        while (myString.substring(myString.length-1,myString.length)==" ") myString = myString.substring(0,myString.length-1);

        return myString;
        },


    strpos: function(haystack,needle,offset)
        {
        var i = (' '+haystack+' ').indexOf(needle,offset);
        return i===-1 ? false : i;
        }
    }
