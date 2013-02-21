#!/usr/bin/perl

# Program :  COPS localization string generator 
# Version :  0.0.1
#
# Author  :  Sébastien Lucas
# License :  GPLv2
#

use strict;

my @strings = ();
my %values;
my %allstrings;

# Load php files looking for strings to localize

opendir (my($dirhandle), "../") or die ("Directory not found\n");
for (readdir ($dirhandle)) {
    next if (-d $_ ); # skip directories
    next if (/^[.]/); # skip dot-files
    next if not (/(.+)[.]php$/);
    
    my $file = "../" . $_;
    debug ("text file: " . $_ . "\n");
    my $content = loadFile ($file);
    
    while ($content =~ /localize\s*\("([\w\.]*?)"\)/igs) {
        $allstrings{$1} = "";
        debug (" * $1 \n");
    }
    
    while ($content =~ /localize\s*\("([\w\.]*?)"\s*,/igs) {
        $allstrings{$1 . ".none"} = "";
        $allstrings{$1 . ".one"} = "";
        $allstrings{$1 . ".many"} = "";
        debug (" *** $1 \n");
    }
}
closedir $dirhandle;

@strings = sort (keys (%allstrings));

# Load existing json files with strings and values

opendir (my($dirhandle), "../lang") or die ("Directory not found\n");
for (readdir ($dirhandle)) {
    next if (-d $_ ); # skip directories
    next if (/^[.]/); # skip dot-files
    next if not (/(.+)[.]json$/);
    
    my $file = "../lang/" . $_;    
    (my $lang = $_) =~ s/Localization_(\w\w)\.json/$1/;
    debug ("language file: $_ / $lang \n");
    
    my $content = loadFile ($file);
    
    while ($content =~ /"(.*?)"\:"(.*?)",/igs) {
        #push @strings, $1;
        $values{$lang}{$1} = $2;
        #debug (" * $1 \n");
    }
    
    open OUTPUT, ">$file.new";
    
    print OUTPUT "{\n";
    foreach my $name (@strings) {
        print OUTPUT "\"$name\":\"$values{$lang}{$name}\",\n";
    }
    print OUTPUT "\"end\":\"end\"\n";
    print OUTPUT "}\n";
    
    close OUTPUT;
}
closedir $dirhandle;



sub loadFile {
    my ($file) = @_;
    my $save = $/;
    $/ = undef;
    
    open INPUT, "<$file";
    my $content = <INPUT>;
    close INPUT;

    $/ = $save;
    
    return $content;
}

sub debug {
    #uncomment next line for debug messages
    print @_;
}