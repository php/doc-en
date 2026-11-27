"""Print the extension labels of the manual pages an issue refers to.

Reads the issue body from the BODY environment variable and writes a
GITHUB_OUTPUT block named "labels", one label per line.
"""

import os
import re
import subprocess

# reference/ directories whose label is not "Extension: <directory>".
LABELS = {
    "apache": "SAPI: apache2handler",
    "bc": "Extension: bcmath",
    "bzip2": "Extension: bz2",
    "com": "Extension: com_dotnet",
    "datetime": "Extension: date",
    "fpm": "SAPI: FPM",
    "image": "Extension: gd",
    "sem": "Extension: System V",
    "simplexml": "Extension: SimpleXml",
    "xmlreader": "Extension: xml-reader",
    "xmlwriter": "Extension: xml-writer",
}
for directory in (
    "pdo_dblib", "pdo_firebird", "pdo_mysql", "pdo_oci", "pdo_odbc",
    "pdo_pgsql", "pdo_sqlite",
):
    LABELS[directory] = "Extension: pdo"
for directory in (
    "array", "dir", "exec", "filesystem", "info", "mail", "math", "misc",
    "network", "outcontrol", "password", "stream", "strings", "url", "var",
):
    LABELS[directory] = "Extension: standard"

# Pages mixing the engine and several extensions get no label.
UNLABELED = {"classobj", "errorfunc", "funchand"}

# https://www.php.net/manual/en/function.strlen.php, https://php.net/function.strlen
URL = re.compile(
    r"php\.net/(?:manual/[a-z_]+/)?([a-z0-9_.-]+?)(?:\.php)?(?=[\s#?)\]>\"'`]|$)",
    re.IGNORECASE,
)
PATH = re.compile(r"(?<![\w/])reference/([a-z0-9_]+)/")


def directory_of(page_id):
    """Return the reference/ directory holding the given xml:id."""
    result = subprocess.run(
        ["grep", "-rliF", "--include=*.xml", f'xml:id="{page_id}"', "reference"],
        capture_output=True,
        text=True,
        check=False,
    )
    files = result.stdout.split()
    return files[0].split("/")[1] if files else None


body = os.environ.get("BODY", "")
directories = set(PATH.findall(body))
for page_id in URL.findall(body):
    directory = directory_of(page_id)
    if directory:
        directories.add(directory)

labels = sorted({
    LABELS.get(directory, f"Extension: {directory}")
    for directory in directories
    if directory not in UNLABELED
})

print("labels<<EOF")
for label in labels:
    print(label)
print("EOF")
