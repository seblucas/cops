#!/usr/bin/perl

# Program :  COPS localization string generator
# Version :  0.0.1
#
# Author  :  Sébastien Lucas
# License :  GPLv2
#

use strict;

our @strings = ();
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

handleLanguageFile ("Localization_en.json");

opendir (my($dirhandle), "../lang") or die ("Directory not found\n");
for (readdir ($dirhandle)) {
    next if (-d $_ ); # skip directories
    next if (/^[.]/); # skip dot-files
    next if not (/(.+)[.]json$/);
    next if (/en\.json$/);

    handleLanguageFile ($_);
}
closedir $dirhandle;

sub handleLanguageFile {
    my ($file) = @_;
    (my $lang = $file) =~ s/Localization_(\w\w)\.json/$1/;
    my $file = "../lang/" . $file;
    my $total = 0;
    my $translated = 0;

    debug ("language file: $file / $lang \n");

    my $content = loadFile ($file);

    while ($content =~ /"\s*(.*?)"\:\s*"(.*?)",/igs) {
        my $key = $1;
        my $value = $2;
        next if ($key =~ /^##TODO##/);
        if ($lang eq "en" && $key =~ /^languages\.\w{3}$/) {
            push (@strings, $key);
        }
        $values{$lang}{$key} = $value;
        #debug (" * $1 \n");
    }

    open OUTPUT, ">$file";

    print OUTPUT "{\n";
    foreach my $name (@strings) {
        $total++ if ($name !~ /^languages\.\w{3}$/);
        if (not exists ($values{$lang}{$name})) {
            print OUTPUT "    \"##TODO##$name\": \"$values{en}{$name}\",\n";
        } else {
            $translated++  if ($name !~ /^languages\.\w{3}$/);
            print OUTPUT "    \"$name\": \"$values{$lang}{$name}\",\n";
        }
    }
    my $percentage = ($translated * 100) / $total;
    debug ("  $translated / $total ($percentage %) \n");
    print OUTPUT "    \"DO_NOT_TRANSLATE\": \"end\"\n";
    print OUTPUT "}\n";

    close OUTPUT;
}

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