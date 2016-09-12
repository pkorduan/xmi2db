require 'xml'
require 'json'
#infile = ARGV[0]
#infile = '/var/www/data/alkis/postnas/test2.gml'
infile = '/var/www/data/alkis/postnas/Bestandsdaten.xml'
#outfile = '/var/www/data/alkis/postnas/test2_renamed.gml'
outfile = '/var/www/data/alkis/postnas/Bestandsdaten_renamed.xml'
renamefile = '/var/www/apps/xmi2db/conf/umbenenn.json'

#puts Time.new
start = Time.now

r = XML::Reader.file infile
d = XML::Document.file infile
w = XML::Writer.file outfile
w.set_indent('  ')
u = File.read(renamefile)
rename_list = JSON.parse(u)
first_row = true
last_depth = 0
rename_path = []
#Wurzelelement (z.B. 'AX_Bestandsdatenauszug') soll nicht in den Pfad
root = d.root
#puts root.name

replcmnts = 0

# read and write content
while r.read

	# start the document
	if first_row
		w.start_document(:encoding => r.encoding)
		first_row = false
	end
	
	#wenn tiefe größer als bisher gespeicherte Tiefe, füge Element dem Array "rename_path" hinzu (push)
	#füge 'AX_Bestandsdatenauszug' nicht in den Pfand ein
	if r.depth > last_depth
		if r.name != root.name && r.name != 'enthaelt' && r.name !='geaenderteObjekte' && !r.name["wfs"] && !r.name["gml:featureMember"]
			#puts "Pushe #{r.name}"
			rename_path.push r.name
		end
	#wenn auf tiefster ebene lösche letztes element (pop) und füge Element dem Array "rename_path" hinzu (push)
	elsif r.depth == last_depth
		rename_path.pop
		#TODO: Warum funktioniert das mit 'wfs' aber nicht mit 'gml'?
		if r.name != root.name && r.name != 'enthaelt' && r.name !='geaenderteObjekte' && !r.name["wfs"] && !r.name["gml:featureMember"] #&& !r.name["gml"]
			#puts "Pushe #{r.name}"
			rename_path.push r.name
		end
		#puts rename_path.join('_')
	#wenn tiefe kleiner als bisher gespeicherte Tiefe, lösche letztes element (pop)
	else
		rename_path.pop
	end

	#Prüfe, ob ersetzt werden muss
	#Setze "name" auf Wert aus der Umbennungsliste, wenn er gefunden wurde
	if rename_list.key? rename_path.join('_')
		name = rename_list[rename_path.join('_')]
		#name = rename_list.assoc(rename_path)
		#puts "FOUND IT!!! #{name}"
		replcmnts += 1
	#wird er nicht gefunden, behalten ursprüngliches Element
	else
		name = r.name
	end
	last_depth = r.depth


	#TEST
	if rename_path.join('_').include? "gemarkung"
		puts "NAME #{r.name}"
		puts "PFAD #{rename_path.join('_')}"
	end
	
	case r.node_type
		when XML::Reader::TYPE_COMMENT
			w.write_comment r.value
		when XML::Reader::TYPE_ELEMENT
			content = r.read_string
			#name = r.name
			#if name == 'land'
				#name = 'renamed_land'
			#end
			w.start_element name
			r.attribute_count.times do |i|
				r.move_to_next_attribute
				w.write_attribute r.name, r.value
			end
			w.end_element if content.nil?
		when XML::Reader::TYPE_TEXT
			w.write_raw r.value
		when XML::Reader::TYPE_END_ELEMENT
			w.end_element
	end
end
#puts Time.new
finish = Time.now
diff = finish - start
puts "Ausfuehrungszeit #{diff}"
puts "Ersetzungen #{replcmnts/2}"

s = "AX_Flurstueck"
if s =~ /gml/
	puts "true"
end

=begin
require 'xml'
require 'json'
#infile = ARGV[0]
infile = '/var/www/data/alkis/postnas/nas_mini.xml'
outfile = '/var/www/data/alkis/postnas/nas_minni_renamed.xml'
renamefile = '/var/www/apps/xmi2db/conf/umbenenn.json'

puts Time.new

=begin
r = XML::Reader.file infile
u = File.read(renamefile)
rename_list = JSON.parse(u)
w = XML::Writer.file outfile
w.set_indent('  ')
first_row = true
last_depth = 0
rename_path = []

# read and write content
while r.read

	# start the document
	if first_row
		w.start_document(:encoding => r.encoding)
		first_row = false
	end

	case r.node_type
	when XML::Reader::TYPE_COMMENT
		w.write_comment r.value
	when XML::Reader::TYPE_ELEMENT

=begin
		if r.depth > last_depth
			rename_path.push r.name
		elsif r.depth == last_depth
			rename_path.pop
			rename_path.push r.name
		else
			rename_path.pop
		end

#		if rename_list.key? rename_path.join('_')
#			name = rename_list[rename_path]
#		else
#			name = r.name
#		end
#		last_depth = r.depth

#		w.start_element name
		w.start_element r.name
		r.attribute_count.times do |i|
			r.move_to_next_attribute
			w.write_attribute r.name, r.value
		end

		w.end_element if r.read_string.nil?
	when XML::Reader::TYPE_TEXT
		w.write_raw r.value
	when XML::Reader::TYPE_END_ELEMENT
		w.end_element
	end
end
puts Time.new
=end