#!/usr/bin/perl

use strict;
use warnings;

my %table;
my $table;
$table = "_";
while(<>) {
	if(/^CREATE (TABLE|VIEW) (\S+) /) {
		$table = $2;
		$table = "zzzz$table" if $1 eq "VIEW";
	}
	$table{$table} .= $_;
}

print $table{'_'};
delete $table{'_'};

print "\n$table{$_}" foreach sort keys %table;
