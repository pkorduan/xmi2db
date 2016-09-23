renamefile = File.expand_path(File.dirname(__FILE__)) + '/../conf/umbenenn.json'

require 'xml'
require 'json'

def usage
	puts 'Usage: ruby rename_nas.rb infile [outfile]'
	puts 'Benennt Elemente in NAS-Datei infile für PostNAS Konverter in outfile um.'
end


infile = ARGV[0] || ''
if !(File.exist? infile)
  usage
	puts "Abbruch! Kann Eingabedatei #{infile} nicht finden."
	exit
end

outfile = ARGV[1] || ''
if !File.exist? outfile
	outfile  = "#{File.dirname(infile)}/#{File.basename(infile, ".*")}_renamed#{File.extname(infile)}"
end

start = Time.now

r = XML::Reader.file infile
w = XML::Writer.file outfile
w.set_indent('  ')
u = File.read(renamefile)
new_tag_names = JSON.parse(u)
first_row = true
last_depth = 0
elements = []

replcmnts = 0

while r.read
	# start the document
	if first_row
		w.start_document(:encoding => r.encoding)
		first_row = false
	end

	name = r.name

	# umzubenennende Features sind erst ab Tiefe 4
	if r.depth > 3
		if r.depth > last_depth
			elements.push r.name 
		elsif r.depth == last_depth
			elements.pop
			elements.push r.name
		else
			elements.pop
		end

		rename_path = elements.join('_')
		if new_tag_names.key? rename_path
			name = new_tag_names[rename_path]
			replcmnts += 1
		end
		last_depth = r.depth
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
			w.write_raw r.value.encode(:xml => :text)
		when XML::Reader::TYPE_END_ELEMENT
			w.end_element
	end
end

finish = Time.now

puts "Ausgabedatei: #{outfile}"
puts "Ausfuehrungszeit #{(finish - start).round(2)} Sekunden"
puts "Ersetzungen #{replcmnts/2}"