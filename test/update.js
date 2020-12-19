const path = require("path");
const fs = require("fs");

(() => {
    const arguments = process.argv;
    var trace = arguments && arguments.length && arguments.length > 2 ? fs.readFileSync(arguments[2], "utf-8") : "";
    if (!trace) {
        console.log("usage: " + path.basename(__filename) + " <trace> <target>");
        return;
    }

    var testFiles;
    if (arguments.length > 3) {
        testFiles = [arguments[3]];
        if (!fs.existsSync(testFiles[0])) {
            console.log("\ttarget not exists");
            return;
        }
        if (!fs.statSync(testFiles[0]).isFile()) {
            console.log("\ttarget is not a file");
            return;
        }
    } else {
        testFiles = fs.readdirSync(".");
        testFiles = testFiles.filter((file) => {
            return file.match(/\.http$/);
        });
        if (!testFiles
            || !testFiles.length) {
            console.log("\tno test files found");
            return;
        }
        testFiles.sort();
    }

    trace = trace.replace(/[\r\n]+\t[^\r\n]+/gm, "");
    trace = trace.replace(/([\r\n])[\r\n]+([\r\n])/gm, "$1$2");
    trace = trace.split(/[\r\n]+/);

    var pattern = /(\("Trace-Composite-Hash"\)\s*===\s*")([a-f0-9]*)(")/g;
    testFiles.forEach((file) => {
        console.log("update of " + file);
        var content = fs.readFileSync(file, "utf-8");
        content = content.replace(pattern, (match, group1, group2, group3) => {
            return group1 + trace.shift() + group3;
        });
        fs.writeFileSync(file, content, null);
    });

    console.log("");
    console.log("Done");
})();