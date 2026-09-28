# W.R.A.P. 5 tools (wrap5-tools package), before the system commands. A
# /opt/wrap/bin added later (development clone, server) still comes first.
case ":$PATH:" in
    *:/usr/local/lib/wrap5-tools/bin:*) ;;
    *) PATH="/usr/local/lib/wrap5-tools/bin:$PATH" ;;
esac
