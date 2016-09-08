require 'xml'
docstream = XML::Reader.file "/var/www/data/alkis/postnas/nas_klein.xml"

while docstream.read
	if docstream.node_type == XML::Reader::TYPE_ELEMENT and !docstream.name.start_with?('gml')
		puts docstream.name
	end
end