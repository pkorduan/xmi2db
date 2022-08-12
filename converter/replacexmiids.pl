#!/usr/bin/perl

use strict;
use warnings;

my %refs;
my %names;
my %ids;

my $file = shift @ARGV;

open I, "$file" or die "File $file not found: $!";

print STDERR "Building map...\n";

my $genid;
my $genstart;
my $genchild;
my $genparent;
my $genstate;
my %gen;

sub add {
	my ($id, $name, $line) = @_;

	if(exists $ids{$name} && $ids{$name}->{id} ne $id) {
		# warn "name $name at $. already registered as $ids{$name}->{id} instead of $id at $ids{$name}->{line} [$_]";

		my $i = 1;
		while(exists $ids{"$name.$i"} && $ids{"$name.$i"}->{id} ne $id) {
			$i++;
		}
		$name = "$name.$i"
	}

	die "id $id at $. already registered as $names{$id}->{name} instead of $name at $names{$id}->{line} [$_]" if exists $names{$id} && $names{$id}->{name} ne $name;

	die "name undefined" unless defined $name;

	$names{$id} = { name=>$name, line=>$line };
	$ids{$name} = { id=>$id, line =>$line };

	die "name undefined" unless defined $names{$id}->{name};
}

while(<I>) {
	chomp;
	my($ref) = /xmi\.idref="([^"]+)"/;
	$refs{$ref}=1 if defined $ref;

	my ($element) = /^\s*<(?:UML:)?([^\/>\s]+)/;
	next unless defined $element;

	my($id) = /xmi\.id="([^"]+)"/;

	if($element eq "Generalization" && defined $id ) {
		die "genid $genid already defined [$genstart -> $.:$_]" if defined $genid;

		$genid = $id;
		$genstart = $.;
		undef $genchild;
		undef $genparent;
		$genstate = "undef";
		next;
	}

	my $name;
	if(defined $genid) {
		if( $element eq "Generalization.child" ) {
			$genstate = "child";

		} elsif( $element eq "Generalization.parent" ) {
			$genstate = "parent";

		} elsif( $element =~ /^(Interface|Class|ClassifierRole|AssociationClass)$/ && defined $ref ) {
			if($genstate eq "child") {
				die "child already $genchild not $ref [$genstart -> $.:$_]" if defined $genchild;
				$genchild = $ref;
			} elsif($genstate eq "parent") {
				die "parent already $genparent not $ref [$genstart -> $.:$_]" if defined $genparent;
				$genparent = $ref;
			} else {
				die "invalid genstate $genstate";
			}

			if(defined $genchild && defined $genparent) {
							$gen{$genid} = { child=>$genchild, parent=>$genparent, line=>$genstart };
							undef $genid;
							undef $genchild;
							undef $genparent;
							undef $genstate;
							undef $genstart;
			}
		}

		next;
	} else {
		warn "name=GM_Arc!" if /name="GM_Arc"/;

		($name) = /name="([^"]+)"/;
		next unless defined $name;

		warn "name=GM_Arc!" if /name="GM_Arc"/;

		$name = $element . "." . $name;
	}

	next unless defined $id;

	warn "name $name at $. already registered as $id on $ids{$name}->{line}" if exists $ids{$name} && $ids{$name}->{id} eq $id;
	warn "id $id already registered as $name [$_] on $names{$id}->{line}" if exists $names{$id} && $names{$id}->{name} eq $name;

	add($id, $name, $.);
}

foreach my $id (keys %gen) {
	my $child = $gen{$id}->{child};
	$child = $names{$child}->{name} if exists $names{$child};
	my $parent = $gen{$id}->{parent};
	$parent = $names{$parent}->{name} if exists $names{$parent};

	my $name = "Generalization.$parent.$child";
	add($id, $name, $gen{$id}->{line});
}

my $lines = $.;
print STDERR "$lines lines processed\n";

foreach my $ref (sort keys %refs) {
	next if exists $names{$ref};
	warn "Referenced id $ref not found\n";
	delete $refs{$ref};
}

my $n = keys %refs;

print STDERR "Replacing $n ids...\n";

close I;
open I, "$file" or die "File $file not found: $!";

open O, ">$file.xml";

my $s = qr/@{[join '|', map { '"' . quotemeta($_)  . '"' } keys %refs]}/;
my %r;
$r{'"' . $_ . '"'} = '"' . $names{$_}->{name} . '"' foreach keys %refs;

my $l = 0;
while(<I>) {
	my $p = int($. * 100 / $lines);
	print STDERR "$. lines processed [$p%]\r" if $p > $l || $. % 100 == 0;
	$l = $p;
	s/($s)/$r{$1}/g;
	print O;
}
print O "<!-- vim: set ts=2 nowrap: -->\r\n";
close O;

close I;
