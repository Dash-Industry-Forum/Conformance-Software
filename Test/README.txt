
About the tool:

This is an automation test comparing the reference (old) and processed (new) results. It's used after the modification of the software to verify if the differences between the results are desired. 

The reference should be stored under ../webfe/TestResults/References. If you want to make new references, please clean contents in this folder, or check "Create Reference". It tests all the test vectors with a full conformance check.

Besides this tool, the following things should be done manually:

1. test with "MPD conformance only" checked.

2. test with uploaded MPD file.

3. test with multiple tabs or browsers.

4. test with "?mpdurl=...".

5. test with local server and remote server.