<?xml version="1.0" encoding="UTF-8"?><schema xmlns="http://purl.oclc.org/dsdl/schematron" xmlns:dash="urn:mpeg:dash:schema:mpd:2011" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink" queryBinding="xslt" schemaVersion="ISO19757-3">
	<ns prefix="dash" uri="urn:mpeg:dash:schema:mpd:2011"/>
	<ns prefix="xlink" uri="http://www.w3.org/1999/xlink"/>
	<ns prefix="xsi" uri="http://www.w3.org/2001/XMLSchema-instance"/>
	<title>Schema for validating MPDs</title>
	<pattern name="MPD element">
		<!-- R1.*: Check the conformance of MPD -->
		<rule context="dash:MPD">
			<!-- R1.0 -->
			<assert test="if (@type = 'dynamic' and not(@availabilityStartTime)) then false() else true()">If MPD is of type "dynamic" availabilityStartTime shall be defined.</assert>
			<!-- R1.1 -->
			<assert test="if (@type = 'dynamic' and not(@publishTime)) then false() else true()">If MPD is of type "dynamic" publishTime shall be defined.</assert>
			<!-- R1.2 -->
			<assert test="if (@type = 'static' and @timeShiftBufferDepth) then false() else true()">If MPD is of type "static" timeShiftBufferDepth shall not be defined.</assert>
			<!-- R1.3 -->
			<assert test="if (@type = 'static' and not(@mediaPresentationDuration)) then false() else true()">If MPD is of type "static" mediaPresentationDuration shall be defined.</assert>
			<!-- R1.4 -->
			<assert test="if (@type = 'static' and descendant::dash:Period[1]/@start and (years-from-duration(descendant::dash:Period[1]/@start) + months-from-duration(descendant::dash:Period[1]/@start) + days-from-duration(descendant::dash:Period[1]/@start) + hours-from-duration(descendant::dash:Period[1]/@start) + minutes-from-duration(descendant::dash:Period[1]/@start) +  seconds-from-duration(descendant::dash:Period[1]/@start)) &gt; 0) then false() else true()">If MPD is of type "static" and the first period has a start attribute the start attribute shall be zero.</assert>
			<!-- R1.5 -->
			<assert test="if (not(@mediaPresentationDuration) and not(@minimumUpdatePeriod)) then false() else true()">If mediaPresentationDuration is not defined for the MPD minimumUpdatePeriod shall be defined or vice versa.</assert>
			<!-- R1.6 -->
			<assert test="if (@type = 'static' and @minimumUpdatePeriod) then false() else true()">If MPD is of type "static" minimumUpdatePeriod shall not be defined.</assert>
			<!-- R1.7 -->
			<assert test="if (not(@profiles) or (contains(@profiles, 'urn:mpeg:dash:profile:isoff-on-demand:2011') or contains(@profiles, 'urn:mpeg:dash:profile:isoff-live:2011') or contains(@profiles, 'urn:mpeg:dash:profile:isoff-main:2011') or contains(@profiles, 'urn:mpeg:dash:profile:full:2011') or contains(@profiles, 'urn:mpeg:dash:profile:mp2t-main:2011') or contains(@profiles, 'urn:mpeg:dash:profile:mp2t-simple:2011'))) then true() else false()">The On-Demand profile shall be identified by the URN "urn:mpeg:dash:profile:isoff-on-demand:2011". The live profile shall be identified by the URN "urn:mpeg:dash:profile:isoff-live:2011". The main profile shall be identified by the URN "urn:mpeg:dash:profile:isoff-main:2011". The full profile shall be identified by the URN "urn:mpeg:dash:profile:full:2011". The mp2t-main profile shall be identified by the URN "urn:mpeg:dash:profile:mp2t-main:2011". The mp2t-simple profile shall be identified by the URN "urn:mpeg:dash:profile:mp2t-simple:2011".</assert>
			<!-- R1.8 -->
			<assert test="if (not(contains(@profiles, 'urn:mpeg:dash:profile:isoff-on-demand:2011')) or not(@type) or @type='static') then true() else false()">For On-Demand profile, the MPD @type shall be "static".</assert>
            <!-- R1.9 -->
            <assert test="if (not(@mediaPresentationDuration) and not(@minimumUpdatePeriod) and not(dash:Period[last()]/@duration)) then false() else true()">If minimumUpdatePeriod is not present and the last period does not include the duration attribute the mediaPresentationDuration must be present.</assert>
            <!-- R1.10: Disabled, there is no such conformance point in DASH 2nd edition (cuurent) -->
            <!-- assert test="if (@type='dynamic' and not(@id)) then false() else true()">If the MPD type is dynamic, the id shall be present </assert-->		
		</rule>
	</pattern>
	<pattern name="Period element">
		<!-- R2.*: Check the conformance of Period -->
		<rule context="dash:Period">
			<!-- R2.0 -->
			<assert test="if (string(@bitstreamSwitching) = 'true' and string(child::dash:AdaptationSet/@bitstreamSwitching) = 'false') then false() else true()">If bitstreamSwitching is set to true all bitstreamSwitching declarations for AdaptationSet within this Period shall not be set to false.</assert>
			<!-- R2.1 -->
			<assert test="if (@id = preceding::dash:Period/@id) then false() else true()">The id of each Period shall be unique.</assert>
			<!-- R2.2: This rule has been found to not work properly, hence disabled for now -->
			<!-- assert test="if ((years-from-duration(@start) + months-from-duration(@start) + days-from-duration(@start) + hours-from-duration(@start) + minutes-from-duration(@start) +  seconds-from-duration(@start)) > (years-from-duration(following-sibling::dash:Period/@start) + months-from-duration(following-sibling::dash:Period/@start) + days-from-duration(following-sibling::dash:Period/@start) + hours-from-duration(following-sibling::dash:Period/@start) + minutes-from-duration(following-sibling::dash:Period/@start) +  seconds-from-duration(following-sibling::dash:Period/@start))) then false() else true()">Periods shall be physically ordered in the MPD file in increasing order of their start time.</assert-->
			<!-- R2.3 -->
			<assert test="if ((child::dash:SegmentBase and child::dash:SegmentTemplate and child::dash:SegmentList) or (child::dash:SegmentBase and child::dash:SegmentTemplate) or (child::dash:SegmentBase and child::dash:SegmentList) or (child::dash:SegmentTemplate and child::dash:SegmentList)) then false() else true()">At most one of SegmentBase, SegmentTemplate and SegmentList shall be defined in Period.</assert>
			<!-- R2.4 -->
			<assert test="if (not(@id) and ancestor::dash:MPD/@type = 'dynamic') then false() else true()">If the MPD is dynamic the Period element shall have an id.</assert>
			<!-- R2.5 -->
			<assert test="if (not(descendant-or-self::dash:BaseURL) and not(descendant-or-self::dash:SegmentTemplate) and not(descendant-or-self::dash:SegmentList)) then false() else true()">At least one BaseURL, SegmentTemplate or SegmentList shall be defined in Period, AdaptationSet or Representation.</assert>
            		<assert test="if (@duration = 0 and count(child::dash:AdaptationSet)) then false() else true()">If the duration attribute is set to zero, there should only be a single AdaptationSet present.</assert>
			<!-- RD2.0	DASH264 -->
			<assert test="if (contains(ancestor::dash:MPD/@profiles, 'http://dashif.org/guidelines/dash') and dash:SegmentList) then false() else true()">DASH264 Section 3.2.2: "the Period.SegmentList element shall not be present" violated here </assert>
			<!-- RD2.1	DASH264 -->
			<assert test="if (contains(ancestor::dash:MPD/@profiles, 'http://dashif.org/guidelines/dash') and (count(child::dash:AdaptationSet[@contentType='video']) &gt; 1) and (count(descendant::dash:Role[@value='main'])=0)) then false() else true()"> DASH264 Section 3.2.2: "	If a Period contains multiple Adaptation Sets with value of the @contentType="video" then at least one Adaptation Set shall contain a Role el-ement $&lt;$Role scheme="urn:mpeg:dash:role:2011" value="main"&gt;" violated here</assert>
		</rule>
	</pattern>
	<pattern name="AdaptationSet element">
		<!-- R3.*: Check the conformance of AdaptationSet -->
		<rule context="dash:AdaptationSet">
			<!-- R3.0 -->
			<assert test="if (@id = preceding-sibling::dash:AdaptationSet/@id) then false() else true()">The id of each AdaptationSet within a Period shall be unique.</assert>
			<!-- R3.1 -->
			<assert test="if ((@lang = descendant::dash:ContentComponent/@lang) or (@contentType = descendant::dash:ContentComponent/@contentType) or (@par = descendant::dash:ContentComponent/@par)) then false() else true()">Attributes from the AdaptationSet shall not be repeated in the descendanding ContentComponent elements.</assert>
			<!-- R3.2 -->
			<assert test="if ((@profiles and descendant::dash:Representation/@profiles) or (@width and descendant::dash:Representation/@width) or (@height and descendant::dash:Representation/@height) or (@sar and descendant::dash:Representation/@sar) or (@frameRate and descendant::dash:Representation/@frameRate) or (@audioSamplingRate and descendant::dash:Representation/@audioSamplingRate) or (@mimeType and descendant::dash:Representation/@mimeType) or (@segmentProfiles and descendant::dash:Representation/@segmentProfiles) or (@codecs and descendant::dash:Representation/@codecs) or (@maximumSAPPeriod and descendant::dash:Representation/@maximumSAPPeriod) or (@startWithSAP and descendant::dash:Representation/@startWithSAP) or (@maxPlayoutRate and descendant::dash:Representation/@maxPlayoutRate) or (@codingDependency and descendant::dash:Representation/@codingDependency) or (@scanType and descendant::dash:Representation/@scanType)) then false() else true()">Common attributes for AdaptationSet and Representation shall either be in one of the elements but not in both.</assert>
			<!-- R3.3 -->
			<assert test="if ((@minWidth &gt; @maxWidth) or (@minHeight &gt; @maxHeight) or (@minBandwidth &gt; @maxBandwidth)) then false() else true()">Each minimum value (minWidth, minHeight, minBandwidth) shall be larger than the maximum value.</assert>
			<!-- R3.4 -->
			<assert test="if (descendant::dash:Representation/@bandwidth &lt; @minBandwidth or descendant::dash:Representation/@bandwidth &gt; @maxBandwidth) then false() else true()">The value of the bandwidth attribute shall be in the range defined by the AdaptationSet.</assert>
			<!-- R3.5 -->
			<assert test="if (descendant::dash:Representation/@width &lt; @minWidth or descendant::dash:Representation/@width &gt; @maxWidth) then false() else true()">The value of the width attribute shall be in the range defined by the AdaptationSet.</assert>
			<!-- R3.6 -->
			<assert test="if (descendant::dash:Representation/@height &lt; @minHeight or descendant::dash:Representation/@height &gt; @maxHeight) then false() else true()">The value of the height attribute shall be in the range defined by the AdaptationSet.</assert>
			<!-- R3.7 -->
			<assert test="if (count(child::dash:Representation)=0) then false() else true()">An AdaptationSet shall have at least one Representation element.</assert>
			<!-- R3.8 -->
			<assert test="if ((child::dash:SegmentBase and child::dash:SegmentTemplate and child::dash:SegmentList) or (child::dash:SegmentBase and child::dash:SegmentTemplate) or (child::dash:SegmentBase and child::dash:SegmentList) or (child::dash:SegmentTemplate and child::dash:SegmentList)) then false() else true()">At most one of SegmentBase, SegmentTemplate and SegmentList shall be defined in AdaptationSet.</assert>
			<!-- RD3.0	DASH264 -->
			<assert test="if (contains(ancestor::dash:MPD/@profiles, 'http://dashif.org/guidelines/dash') and @contentType='video' and not(@par)) then false() else true()"> DASH264 Section 3.2.4: "For any Adaptation Sets with value of the @contentType="video" the following attributes shall be present: ... @par" violated here</assert>
			<!-- RD3.1	DASH264 -->
			<assert test="if (contains(ancestor::dash:MPD/@profiles, 'http://dashif.org/guidelines/dash') and @contentType='video' and @scanType) then false() else true()"> DASH264 Section 3.2.4: "For Adaptation Set or for any Representation within an Adaptation Set with value of the @contentType="video" the attribute @scanType must not be present" violated here</assert>
			<!-- RD3.2	DASH264 -->
			<assert test="if (contains(ancestor::dash:MPD/@profiles, 'http://dashif.org/guidelines/dash') and @contentType='audio' and not(@lang)) then false() else true()"> DASH264 Section 3.2.4: "For any Adaptation Sets with value of the @contentType="audio" the following attributes shall be present: @lang" violated here</assert>
		</rule>
	</pattern>

	<pattern name="ContentComponent element">
		<!-- R4.*: Check the conformance of ContentComponent -->
		<rule context="dash:ContentComponent">
			<!-- R4.0 -->
			<assert test="if (@id = preceding-sibling::dash:ContentComponent/@id) then false() else true()">The id of each ContentComponent within an AdaptationSet shall be unique.</assert>
		</rule>
	</pattern>
	<pattern name="Representation element">
		<!-- R5.*: Check the conformance of Representation -->
		<rule context="dash:Representation">
			<!-- R5.0 -->
			<assert test="if (not(@mimeType) and not(parent::dash:AdaptationSet/@mimeType)) then false() else true()">Either the Representation or the containing AdaptationSet shall have the mimeType attribute.</assert>
			<!-- R5.1 -->
			<assert test="if (not(child::dash:SegmentTemplate or parent::dash:AdaptationSet/dash:SegmentTemplate or ancestor::dash:Period/dash:SegmentTemplate) and (contains(@profiles, 'urn:mpeg:dash:profile:isoff-live:2011') or contains(ancestor::dash:MPD/@profiles, 'urn:mpeg:dash:profile:isoff-live:2011') or contains(parent::dash:AdaptationSet/@profiles, 'urn:mpeg:dash:profile:isoff-live:2011'))) then false() else true()">For live profile, the SegmentTemplate element shall be present on at least one of the three levels, the Period level containing the Representation, the Adaptation Set containing the Representation, or on Representation level itself.</assert>
			<!-- R5.2 -->
			<assert test="if ((child::dash:SegmentBase and child::dash:SegmentTemplate and child::dash:SegmentList) or (child::dash:SegmentBase and child::dash:SegmentTemplate) or (child::dash:SegmentBase and child::dash:SegmentList) or (child::dash:SegmentTemplate and child::dash:SegmentList)) then false() else true()">At most one of SegmentBase, SegmentTemplate and SegmentList shall be defined in Representation.</assert>
			<!-- RD5.0	DASH264 -->
			<assert test="if (contains(ancestor::dash:MPD/@profiles, 'http://dashif.org/guidelines/dash') and parent::dash:AdaptationSet/@contentType='video' and (((@width != preceding-sibling::dash:Representation/@width) and not(parent::dash:AdaptationSet/@maxWidth)) or ((@height != preceding-sibling::dash:Representation/@height) and not(parent::dash:AdaptationSet/@maxHeight)) or ((@frameRate != preceding-sibling::dash:Representation/@frameRate) and not(parent::dash:AdaptationSet/@maxFrameRate)))) then false() else true()"> DASH264 Section 3.2.4: "For any Adaptation Sets with value of the @contentType="video" the following attributes shall be present: @maxWidth (or @width if all Representations have the same width), @maxHeight (or @height if all Representations have the same width), @maxFrameRate (or @frameRate if all Representations have the same width)" violated here</assert>
			<!-- RD5.1	DASH264 -->
			<assert test="if (contains(ancestor::dash:MPD/@profiles, 'http://dashif.org/guidelines/dash') and parent::dash:AdaptationSet/@contentType='video' and ((not(@width) and not(parent::dash:AdaptationSet/@width)) or (not(@height) and not(parent::dash:AdaptationSet/@height)) or (not(@frameRate) and not(parent::dash:AdaptationSet/@frameRate)) or not(@sar))) then false() else true()"> DASH264 Section 3.2.4: "For any Representation within an Adaptation Set with value of the @contentType="video" the following attributes shall be present: @width, if not present in AdaptationSet element; @height, if not present in AdaptationSet element; @frameRate, if not present in AdaptationSet element; @sar" violated here</assert>
			<!-- RD5.2	DASH264 -->
			<assert test="if (contains(ancestor::dash:MPD/@profiles, 'http://dashif.org/guidelines/dash') and parent::dash:AdaptationSet/@contentType='video' and @scanType) then false() else true()"> DASH264 Section 3.2.4: "For Adaptation Set or for any Representation within an Adaptation Set with value of the @contentType="video" the attribute @scanType must not be present" violated here</assert>
			<!-- RD5.3	DASH264 -->
			<assert test="if (contains(ancestor::dash:MPD/@profiles, 'http://dashif.org/guidelines/dash') and parent::dash:AdaptationSet/@contentType='audio' and ((not(@audioSamplingRate) and not(parent::dash:AdaptationSet/@audioSamplingRate)) or (not(dash:AudioChannelConfiguration) and not(parent::dash:AdaptationSet/dash:AudioChannelConfiguration)))) then false() else true()"> DASH264 Section 3.2.4: "o	For any Representation within an Adaptation Set with value of the @contentType="audio" the following elements and attributes shall be present: @audioSamplingRate, if not present in AdaptationSet element; AudioChannelConfiguration, if not present in AdaptationSet element" violated here</assert>
		</rule>
	</pattern>
	<pattern name="SubRepresentation element">
		<!-- R6.*: Check the conformance of SubRepresentation -->
		<rule context="dash:SubRepresentation">
			<!-- R6.0 -->
			<assert test="if (@level and not(@bandwidth)) then false() else true()">If the level attribute is defined for a SubRepresentation also the bandwidth attribute shall be defined.</assert>
		</rule>
	</pattern>
	<pattern name="SegmentTemplate element">
		<!-- R7.*: Check the conformance of SegmentTemplate -->
		<rule context="dash:SegmentTemplate">
			<!-- R7.0 -->
			<assert test="if (not(@duration) and not(child::dash:SegmentTimeline) and not(@initialization) ) then false() else true()">If more than one Media Segment is present the duration attribute or SegmentTimeline element shall be present.</assert>
			<!-- R7.1 -->
			<assert test="if (@duration and child::dash:SegmentTimeline) then false() else true()">Either the duration attribute or SegmentTimeline element shall be present but not both.</assert>
			<!-- R7.2 -->
			<assert test="if (not(@indexRange) and @indexRangeExact) then false() else true()">If indexRange is not present indexRangeExact shall not be present.</assert>
			<!-- R7.3 -->
			<assert test="if (@initialization and (matches(@initialization, '\$Number(%.[^\$]*)?\$') or matches(@initialization, '\$Time(%.[^\$]*)?\$'))) then false() else true()">Neither $Number$ nor the $Time$ identifier shall be included in the initialization attribute.</assert>
			<!-- R7.4 -->
			<assert test="if (@bitstreamSwitching and (matches(@bitstreamSwitching, '\$Number(%.[^\$]*)?\$') or matches(@bitstreamSwitching, '\$Time(%.[^\$]*)?\$'))) then false() else true()">Neither $Number$ nor the $Time$ identifier shall be included in the bitstreamSwitching attribute.</assert>
			<!-- R7.5-->
			<assert test="if (matches(@media, '\$.[^\$]*\$')) then every $y in (for $x in tokenize(@media, '\$(Bandwidth|Time|Number|RepresentationID)(%.[^\$]*)?\$') return matches($x, '\$.[^\$]*\$')) satisfies $y eq false() else true()">Only identifiers such as $Bandwidth$, $Time$, $RepresentationID$, or $Number$ shall be used.</assert>
			<!-- R7.6-->
			<assert test="if (matches(@media, '\$RepresentationID%.[^\$]*\$')) then false() else true()">$RepresentationID$ shall not have a format tag.</assert>
		</rule>
	</pattern>
	<pattern name="SegmentList element">
		<!-- R8.*: Check the conformance of SegmentList -->
		<rule context="dash:SegmentList">
			<!-- R8.0 -->
			<assert test="if (not(@duration) and not(child::dash:SegmentTimeline)) then if (count(child::dash:SegmentURL) &gt; 1) then false() else true() else true()">If more than one Media Segment is present the duration attribute or SegmentTimeline element shall be present.</assert>
			<!-- R8.1 -->
			<assert test="if (@duration and child::dash:SegmentTimeline) then false() else true()">Either the duration attribute or SegmentTimeline element shall be present but not both.</assert>
			<!-- R8.2 -->
			<assert test="if (not(@indexRange) and @indexRangeExact) then false() else true()">If indexRange is not present indexRangeExact shall not be present.</assert>
		</rule>
	</pattern>
	<pattern name="SegmentBase element">
		<!-- R9.*: Check the conformance of SegmentBase -->
		<rule context="dash:SegmentBase">
			<!-- R9.0 -->
			<assert test="if (not(@indexRange) and @indexRangeExact) then false() else true()">If indexRange is not present indexRangeExact shall not be present.</assert>
            <!-- R9.1 -->
            <assert test="if (@timeShiftBufferDepth) then if (@timeShiftbuffer &lt; dash:MPD/@timeShiftBufferDepth) then false() else true() else true()">The timeShiftBufferDepth shall not be smaller than timeShiftBufferDepth specified in the MPD element</assert>
		</rule>
	</pattern>
	<pattern name="SegmentTimeline element">
		<!-- R10.*: Check the conformance of SegmentTimeline -->
		<rule context="dash:SegmentTimeline">
			<!-- R10.0 -->
			<assert test="if ((if (ancestor::dash:*[1]/@timescale) then (child::dash:S/@d div ancestor::dash:*[1]/@timescale) else child::dash:S/@d) &gt; (years-from-duration(ancestor::dash:MPD/@maxSegmentDuration) + months-from-duration(ancestor::dash:MPD/@maxSegmentDuration) + days-from-duration(ancestor::dash:MPD/@maxSegmentDuration) + hours-from-duration(ancestor::dash:MPD/@maxSegmentDuration) + minutes-from-duration(ancestor::dash:MPD/@maxSegmentDuration) +  seconds-from-duration(ancestor::dash:MPD/@maxSegmentDuration))) then false() else true()">The d attribute of a SegmentTimeline shall not exceed the value give bei the MPD maxSegmentDuration attribute.</assert>
		</rule>
	</pattern>
	<pattern name="ProgramInformation element">
		<!-- R11.*: Check the conformance of ProgramInformation -->
		<rule context="dash:ProgramInformation">
			<!-- R11.0 -->
			<assert test="if (count(parent::dash:MPD/dash:ProgramInformation) &gt; 1 and not(@lang)) then false() else true()">If more than one ProgramInformation element is given each ProgramInformation element shall have a lang attribute.</assert>
		</rule>
	</pattern>
	<pattern name="ContentProtection element">
		<!-- R12.*: Check the conformance of ContentProtection -->
		<rule context="dash:ContentProtection">
			<!-- R12.0 -->
			<assert test="if ((@schemeIdUri = 'urn:mpeg:dash:mp4protection:2011') and not(string-length(@value) = 4)) then false() else true()">The value of ContentProtection shall be the 4CC contained in the Scheme Type Box</assert>
			<!-- R12.1 -->
			<assert test="if ((@schemeIdUri = 'urn:mpeg:dash:13818:1:CA_descriptor:2011') and not(string-length(@value) = 4)) then false() else true()">The value of ContentProtection shall be the 4-digit lower-case hexadecimal Representation.</assert>
		</rule>
	</pattern>
	<pattern name="Role element">
		<!-- R13.*: Check the conformance of Role -->
		<rule context="dash:Role">
			<!-- R13.0 -->
			<assert test="if ((@schemeIdUri = 'urn:mpeg:dash:role:2011') and not(@value = 'caption' or @value = 'subtitle' or @value = 'main' or @value = 'alternate' or @value = 'supplementary' or @value = 'commentary' or @value = 'dub')) then false else true()">The value of Role (role) shall be caption, subtitle, main, alternate, supplementary, commentary or dub.</assert>
			<!-- R13.1 -->
			<assert test="if ((@schemeIdUri = 'urn:mpeg:dash:stereoid:2011') and not(starts-with(@value, 'l') or starts-with(@value, 'r'))) then false() else true()">The value of Role (stereoid) shall start with 'l' or 'r'.</assert>
		</rule>
	</pattern>	
	<pattern name="FramePacking element">
		<!-- R14.*: Check the conformance of FramePacking -->
		<rule context="dash:FramePacking">
			<!-- R14.0 -->
			<assert test="if ((@schemeIdUri = 'urn:mpeg:dash:14496:10:frame_packing_arrangement_type:2011') and not(contains(parent::dash:AdaptationSet/@codecs, 'avc') or contains(parent::dash:AdaptationSet/@codecs, 'svc') or contains(parent::dash:AdaptationSet/@codecs, 'mvc')) and not(contains(parent::dash:Representation/@codecs, 'avc') or contains(parent::dash:Representation/@codecs, 'svc') or contains(parent::dash:Representation/@codecs, 'mvc'))) then false() else true()">The URI urn:mpeg:dash:14496:10:frame_packing_arrangement_type:2011 is used for Adaptation Sets or Representations that contain a video component that conforms to ISO/IEC 14496-10.</assert>
			<!-- R14.1 -->
			<assert test="if ((@schemeIdUri = 'urn:mpeg:dash:13818:1:stereo_video_format_type:2011') and not(parent::dash:AdaptationSet/@mimeType = 'video/mp2t') and not(parent::dash:Representation/@mimeType = 'video/mp2t')) then false() else true()">The URI urn:mpeg:dash:13818:1:stereo_video_format_type:2011 is used for Adaptation Sets or Representations that contain a video component that conforms to ISO/IEC 13818-1.</assert>
			<!-- R14.2 -->
			<assert test="if (not(@schemeIdUri = 'urn:mpeg:dash:14496:10:frame_packing_arrangement_type:2011') and not(@schemeIdUri = 'urn:mpeg:dash:13818:1:stereo_video_format_type:2011')) then false() else true()">schemeIdUri for FramePacking descriptor shall be urn:mpeg:dash:14496:10:frame_packing_arrangement_type:2011 or urn:mpeg:dash:13818:1:stereo_video_format_type:2011.</assert>
			<!-- R14.3 -->
			<assert test="if (not(@value = '0' or @value = '1' or @value = '2' or @value = '3' or @value = '4' or @value = '5' or @value = '6')) then false() else true()">The value of FramePacking shall be 0 to 6 as defined in ISO/IEC 23001-8.</assert>
		</rule>
	</pattern>
	<pattern name="AudioChannelConfiguration element">
		<!-- R15.*: Check the conformance of AudioChannelConfiguration -->
		<rule context="dash:AudioChannelConfiguration">
			<!-- R15.0 -->
			<assert test="if ((@schemeIdUri = 'urn:mpeg:dash:outputChannelPositionList:2012') and not(count(tokenize(@value, ' ')) &gt; 1)) then false() else true()">If URI urn:mpeg:dash:outputChannelPositionList:2012 is used the value attribute shall be a space-delimited list as defined in ISO/IEC 23001-8.</assert>
		</rule>
	</pattern>
	
	<pattern name="EventStream element">
		<!-- R16.*: Check the conformance of SegmentList -->
		<rule context="dash:EventStream">
			<!-- R16.0 -->
			<assert test="if (@actuate and not(@href)) then false() else true()">If href is not present actuate shall not be present.</assert>
			<!-- R16.1 -->
			<assert test="if (not(@schemeIdUri)) then false() else true()">schemeIdUri shall be present.</assert>
		</rule>
	</pattern>
    
      <pattern name="Subset element">
        <rule context="dash:Subset">
            <!--R17.1-->
           <assert test="if (@id = preceding::dash:Subset/@id) then false() else true()">The id of each Subset shall be unique.</assert>
        </rule>
    </pattern>
	
	<pattern name="UTCTiming element">
		<!-- R18.*: Check the conformance of UTCTiming -->
		<rule context="dash:UTCTiming">
            <!-- R18.1 -->
			<assert test="if ((@schemeIdUri = 'urn:mpeg:dash:utc:ntp:2014') or (@schemeIdUri = 'urn:mpeg:dash:utc:sntp:2014') or (@schemeIdUri = 'urn:mpeg:dash:utc:http-head:2014') or (@schemeIdUri = 'urn:mpeg:dash:utc:http-xsdate:2014') or (@schemeIdUri = 'urn:mpeg:dash:utc:http-iso:2014') or (@schemeIdUri = 'urn:mpeg:dash:utc:http-ntp:2014') or (@schemeIdUri = 'urn:mpeg:dash:utc:direct:2014')) then true() else false()">@schemeIdUri for UTCTiming is not one of the 7 different types specified.</assert>
		</rule>
	</pattern>			
		
</schema>