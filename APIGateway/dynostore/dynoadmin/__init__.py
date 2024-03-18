"""Top-level package for RP To-Do."""
# dynostore/management/__init__.py

__app_name__ = "DynoStore Admin CLI"
__version__ = "0.1.0"

(
    SUCCESS,
    CREATEADMIN_ERROR,
    CREATEORG_ERROR
) = range(3)

ERRORS = {
    CREATEADMIN_ERROR: "create admin error",
    CREATEORG_ERROR: "create organization error"
}