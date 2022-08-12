#!/usr/bin/perl

use strict;
use warnings;

my %element;

while(<>) {
	if(/^\s*<GMLFeatureClassList>\s*$/) {
		print;
		next;
	}

	last unless /^\s*<GMLFeatureClass>\s*$/;

	my $element = $_;
	my $name;
	while(<>) {
		$element .= $_;
		$name = $1 if /^    <Name>([^<]+)<\/Name>\s*$/;
		last if /^\s*<\/GMLFeatureClass>\s*$/;
	}

	die "noname: $element" unless defined $name;

	$element{$name} = $element;
}

print $element{$_} foreach sort keys %element;
print;
