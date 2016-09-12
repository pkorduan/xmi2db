require 'xml'
require 'json'
#infile = ARGV[0]
infile = '/var/www/data/alkis/postnas/nas_mini.xml'
outfile = '/var/www/data/alkis/postnas/nas_minni_renamed.xml'
renamefile = '/var/www/apps/xmi2db/conf/umbenenn.json'

puts Time.new

r = XML::Reader.file infile
w = XML::Writer.file outfile
w.set_indent('  ')
u = File.read(renamefile)
rename_list = JSON.parse(u)
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

	if r.depth > last_depth
		rename_path.push r.name
	elsif r.depth == last_depth
		rename_path.pop
		rename_path.push r.name
	else
		rename_path.pop
	end

	if rename_list.key? rename_path.join('_')
		name = rename_list[rename_path]
	else
		name = r.name
	end
	last_depth = r.depth

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
puts Time.new


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