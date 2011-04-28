# ex: ts=4 sw=4 et filetype=sh

_phpdoc()
{
    local cur opts
    COMPREPLY=()
    cur="${COMP_WORDS[COMP_CWORD]}"
    opts="
        --help
        --version
        --quiet
        --srcdir=
        --basedir=
        --rootdir=
        --enable-force-dom-save
        --enable-chm
        --enable-howto
        --with-lang=
        --disable-segfault-error
        --disable-segfault-speed
        --disable-version-files
        --disable-libxml-check
        --with-php=
        --with-partial=
        --generate=
        --output=
        --disable-broken-file-listing
        --redirect-stderr-to-stdout"

    if [[ ${cur} == -* ]] ; then
        COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
        return 0
    fi
}

complete -F _phpdoc phpdoc