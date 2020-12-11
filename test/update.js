const path = require("path");
const fs = require("fs");

(() => {
    const arguments = process.argv;
    var trace = arguments && arguments.length && arguments.length > 2 ? fs.readFileSync(arguments[2], "utf-8") : "";
    if (!trace) {
        console.log("usage: " + path.basename(__filename) + " <trace>");
        return;
    }

    var testFiles = fs.readdirSync(".");
    testFiles = testFiles.filter((file) => {
        return file.match(/\.http$/);
    });
    if (!testFiles
            || !testFiles.length) {
        console.log("\tno test files found");
        return;
    }
    testFiles.sort();

    trace = trace.replace(/[\r\n]+\t[^\r\n]+/gm, "");
    trace = trace.replace(/([\r\n])[\r\n]+([\r\n])/gm, "$1$2");
    trace = trace.split(/[\r\n]+/);

    console.log("");
    var pattern = /^([\r\n][^#\r\n].*?\s+===\s+['"])([a-f0-9]{32})(['"].*?[\r\n])/gm;
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