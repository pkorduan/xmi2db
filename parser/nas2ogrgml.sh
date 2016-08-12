#!/bin/bash
#http://stackoverflow.com/a/21959006/6532988
progress(){
    # example usage:
    # progress 30G 9G 30
    # 30G [================>.................................] 30% (9G)

    # params:
    # $1 = total value (e.g.: source size)
    # $2 = current value (e.g.: destination size)
    # $3 = percent completed
    [[ -z $1 || -z $2 || -z $3 ]] && exit  # on empty param...

    percent=$3
    completed=$(( $percent / 2 ))
    remaining=$(( 50 - $completed ))

    echo -ne "\r$1 ["
    printf "%0.s=" `seq $completed`
    echo -n ">"
    [[ $remaining != 0 ]] && printf "%0.s." `seq $remaining`
    echo -n "] $percent% ($2)  "
}

removeQoutes() {
	#echo "vorher $1"
	links=${1%\"*}
	rechts=${links#*\"}
	echo $rechts
}
#http://stackoverflow.com/a/16843375/6532988
buildArrayFromJSON() {
	declare -A array

	while read -r line
	do
		name="$line"
		#echo "Name read from file - $name"
		#Ausgabe der Sachen LINKS vom ":"
		#echo "Links: ${line%:*}"
		resL=$(removeQoutes ${line%:*})
		#echo "Links: $resL"
		#Ausgabe der Sachen RECHTS vom ":"
		#echo "Rechts: ${line##*:}"
		resR=$(removeQoutes ${line##*:})
		#echo "Rechts: $resR"
		array[$resL]=$resR
		#echo ${array[@]}
	done < "$1";
	declare -p array | sed -e 's/^declare -A [^=]*=//'
}

#http://unix.stackexchange.com/a/81113
function remove {
  if [[ $1 =~ ([[:digit:]])(-([[:digit:]]))?   ]]; then
    from=${BASH_REMATCH[1]}
    to=${BASH_REMATCH[3]}
  else
    echo bad range
  fi;shift
  array=( ${@} )
  local start=${array[@]::${from}}
  local rest
  [ -n "$to" ] && rest=${array[@]:((${to}+1))}  || rest=${array[@]:((${from}+1))}
  echo ${start[@]} ${rest[@]}
}

#http://stackoverflow.com/a/4017175/6532988
getKey() {
	declare -a arrInFunc=("${!2}")
	
	for key in ${!arrInFunc[@]}; do
		#echo "Key = $key"
		#echo "Value = ${arrInFunc[$key]}"
		if [[ ${arrInFunc[$key]} == $1 ]]
			then
				keyFound=$key
				#echo "keyFound"
		fi
	done
	return $keyFound
}
#Adresse des JSONs mit den Umbenennungsregeln
#umbenenn=$(curl http://gdi-service.de/xmi2db/umbenennungsliste.php)
umbenenn="umbenenn.json"
#Erzeuge Array aus JSON
arr=$(buildArrayFromJSON $umbenenn)
#echo "arr: $arr"
#Array mit den Regeln heißt dann: arrayUmbenenn
eval "declare -A arrayUmbenenn=${arr}"

filename="$1"
targetFile="$2"
pbarSetting="$3"
#clear file
> $targetFile
pfad=()
log=""
anzZeilenTotal=$(wc -l < $filename)
anzErsetzung=0
anzArrayDurchlauf=0
anzZeilenIgnore=0
anzZeilenIgnoreAfterCase=0
anzZeilenNotIgnoreEndTag=0
anzZeilenNotIgnore=0
i=1
#Problem mit letzter Zeile: http://stackoverflow.com/a/12919766/6532988
while read line || [ -n "$line" ]
do
    #name="$line"
    #echo "Name read from file - $name"
	#echo $line
	
	#Progress Begin
	case "$pbarSetting" in
		none)
			#echo "ProgressBar set to none"
			;;
		mini)
			#echo "ProgressBar set to minimalistic"
			echo -ne "\rZeile $i von $anzZeilenTotal"
			i=$((i + 1))
			;;
		pbar)
			#echo "ProgressBar set to full"
			percent=$(( $i * 100 / $anzZeilenTotal ))
			progress $anzZeilenTotal $i $percent
			i=$((i + 1))
			;;
		*)
			#echo "Wrong value for ProgressBar setting"
			;;
	esac
	#Progress End
	
	
	#Nehme nur Zeilen, die ein "<" enthalten
	if [[ $line == *"<"* ]]
		then
			#echo "Ist da in dieser Zeile: " $line
			foo2=${line##*<}
			#Schneide alles recht vom Leerzeichen ab (aus 'AP_PTO gml:id="DESHLFS300000BTg">' wird 'AP_PTO')
			foo3=${foo2%%" "*}
			#Schneide alles rechts vom '>' ab (aus 'AA_Lebenszeitintervall>' wird 'AA_Lebenszeitintervall')
			foo4=${foo3%%>*}
			#echo $foo3
			case "$line" in
				*xml*|*!*|\<gml*|\</gml*|\<AX_Bestandsdatenauszug*|*IB*|\<auftragsid*|\<profilkennung*|\<antragsnr*|\<nasoperation*|\<operationsart*|\<status*|\<startzeitreal*|\<endzeitreal*|*fehlerobjekt*|\<fehlerbeschreibung*|\<fehlertyp*|*erlaeuterung*|\<erfolgreich*|\<antragsnummer*|*allgemeineAngaben*|*AX_K_Benutzungsergebnis*|*empfaenger*|*AA_Empfaenger*|\<direkt*|*koordinatenangaben*|*AA_Koordinatenreferenzsystemangaben*|\<crs*|\<anzahlDerNachkommastellen*|\<standard*|*enthaelt*|\<wfs*) 
					#echo "schreibe $line in Datei"
					#'>' overwrite, '>>' append
					echo $line >> $targetFile
					anzZeilenIgnoreAfterCase=$((anzZeilenIgnoreAfterCase + 1))
					;;
					*) 
						#damit "/" nicht mitgenommen wird
						#echo $foo4
						foo4=${foo4##*/}
						#echo $foo4
						#Speicher Pfad
						#Alle Zeilen, die mit "</" beginnen zeigen an, dass die max. Pfadtiefe erreicht ist
						
						if [[ $line == "</"* ]]
							then
								#echo "in $line ist Schluss!"
								#pfad=()
								#Lösche Endelemente (z.B. "</AA_Lebenszeitintervall>") aus Pfad
								#echo "Log: Lösche Element $foo4 aus Array '${pfad[@]}'"
								#pfad=( "${pfad[@]/$foo4}" )
								#echo "Finde key '' in "
								getKey $foo4 pfad[@]
								key=$?
								pfad=( `remove $key ${pfad[*]}` )
								#echo "Pfad nach Löschung: ${pfad[@]}"
								log="Log: Lösche $foo4"
								#Schreibe Zeile unverändert zurück
								echo $line >> $targetFile
								anzZeilenNotIgnoreEndTag=$((anzZeilenNotIgnoreEndTag + 1))
							else 
								anzZeilenNotIgnore=$((anzZeilenNotIgnore + 1))
								#echo "Array vorher: ${pfad[@]} (${#pfad[@]})"
								pfad+=($foo4)
								#echo "Log: Erweitere Array und füge $foo4 hinzu"
								#Stelle fest, dass Ende angekommen und übwerschreibe Attribut
								#Zähle Anzahl von '<' (2 bedeutet am Ende der Verschachtelung = max. bzw. tiefste Ebene)
								#http://stackoverflow.com/questions/16679369/count-occurrences-of-char-in-string-using-bash
								res="${line//[^<]}"
								#Bearbeitung von Sachen wie '<dientZurDarstellungVon xlink:href="urn:adv:oid:DESHLFS300000QLn"/>', die sonst im Array bleiben würde, Lösung für "too many arguments": [[ ]] statt [ ]
								#if [[ ${#res} == 2 ]]
								if [[ ${#res} == 2 ]] || [[ $line == *"/>" ]]
									then
										#echo "Pfad: ${pfad[@]}"
										#echo "Pfad Anz: ${#pfad[@]}"
										#Zielpfad (z.B.: AX_Flurstueck_gemarkung_AX_Gemarkung_Schluessel_land)
										zielPfad=$(printf "_%s" "${pfad[@]}")
										zielPfad=${zielPfad:1}
										#echo "Zielpfad: $zielPfad"
										#Prüfe, ob Pfad in ErsetzungsJSON vorkommt
										treffer=false
										#mach das nicht für Zeilen mit "/>"
										#if ! [[ $line == *"/>" ]]
											#then
										#fi
										#Mach das nur für Zeilen in deren Zeile davor ein "_" vorkommt (das ist ja immer die Quelleklasse, z.B. <AX_Gemarkung_Schluessel> 							<land>01</land>
										#echo "Letztes Element: ${pfad[-2]}"
										#wenn array weniger als 2 Elemente hat, kommt sonst "bad array subscript" Fehlermeldung
										if [[ ${#pfad[@]} > 1 ]]
											then
												if [[ ${pfad[-2]} == *"_"* ]]
													then
														case "$line" in *"/>");;
															*)
																for key in ${!arrayUmbenenn[@]}; do
																	#echo "Key = $key"
																	if [[ $key == $zielPfad ]]; then
																		#echo "TREFFER!!!"
																		treffer=true
																	fi
																	#echo "Value = ${arrayUmbenenn[$key]}"
																done
																anzArrayDurchlauf=$((anzArrayDurchlauf + 1))
															;;
														esac
												fi
										fi
										#http://stackoverflow.com/a/14504440/6532988
										case $treffer in
											true)
												#echo "Führe hier Ersetzung durch"
												#TODO: / dabei nicht vergessen! S.o. foo4 ersetzung!
												#echo "Ersetze in Pfad '$zielPfad' das alte '$foo4' mit dem neuen '${arrayUmbenenn[$zielPfad]}'"
												result_string="${line//$foo4/${arrayUmbenenn[$zielPfad]}}"
												#echo "Neue Zeile: $result_string"
												echo $result_string >> $targetFile
												anzErsetzung=$((anzErsetzung + 1))
											;;
											false)   
												#Schreibe Zeile unverändert zurück
												echo $line >> $targetFile
											;;
											#(rm -rf*) echo "I just dodged a bullet";;
										esac
										#echo $result_string
										#echo "Lösche Element ($foo4) aus Array wie oben"
										#pfad=( "${pfad[@]/$foo4}" )
										getKey $foo4 pfad[@]
										key=$?
										pfad=( `remove $key ${pfad[*]}` )
									else
										#Schreibe Zeile unverändert zurück
										echo $line >> $targetFile
								fi
								#echo "Array nachher: ${pfad[@]} (${#pfad[@]})"
						fi
						#echo $log
						#echo "Pfad: ${pfad[@]}"
						#echo "test"
						#Ersetze Pfadangabe "AX_Flurstueck_gemarkung_AX_Gemarkung_Schluessel_land" mit "gemarkung_land" bzw. das Attribut "land" in "gemarkung" mit "gemarkung_land"
						#AX_Flurstueck | gemarkung | AX_Gemarkung_Schluessel | land
					;;
			esac
		else
			#echo "'<' NICHT da!"
			#'>' overwrite, '>>' append
			echo $line >> $targetFile
			anzZeilenIgnore=$((anzZeilenIgnore + 1))
	fi
	#echo "Zeileinhalt: $line"
done < "$filename"
echo ""
echo "Anzahl der gesamten Zahl der Zeilen in der Datei: $anzZeilenTotal"
echo "Anzahl der komplett ignorierten Zeilen: $anzZeilenIgnore"
echo "Anzahl der nach CASE ignorierten Zeilen: $anzZeilenIgnoreAfterCase"
echo "Anzahl der beachteten Zeilen mit '</>': $anzZeilenNotIgnoreEndTag"
echo "Anzahl der richtig geprüften Zeilen: $anzZeilenNotIgnore"
echo "Anzahl der Zeilen, in denen das Umbennungsarrays durchlaufen wurde: $anzArrayDurchlauf"
echo "Anzahl der Zeilen, in denen ersetzt wurde: $anzErsetzung"