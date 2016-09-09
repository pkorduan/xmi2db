require 'xml'
#infile = ARGV[0]
infile = '/var/www/data/alkis/postnas/nas_mini.xml'
outfile = '/var/www/data/alkis/postnas/nas_minni_renamed.xml'

puts Time.new

r = XML::Reader.file infile
w = XML::Writer.file outfile

def write r, w
	case r.node_type
	when XML::Reader::TYPE_COMMENT
		w.write_comment r.value
	when XML::Reader::TYPE_ELEMENT
		content = r.read_string
		name = r.name
		if name == 'land'
			name = 'renamed_land'
		end
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

# set indentation and encoding of the output document
w.set_indent('  ')
r.read
w.start_document(:encoding => r.encoding)
write(r, w)

# read and write content
while r.read
	write r, w
end
puts Time.new
