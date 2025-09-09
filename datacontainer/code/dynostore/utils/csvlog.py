# dynostore/utils/csvlog.py
import logging

def make_csv_logger(service: str, logger_name: str | None = None):
    """
    Returns a _log(operation, key, phase, status, msg="", level="debug") function
    that emits: SERVICE, OPERATION, OBJECTKEY, START/END, Status, MSG
    """
    logger = logging.getLogger(logger_name or service.lower())

    def _log(operation: str, key: str, phase: str, status: str, msg: str = "", level: str = "debug"):
        rec = f"{service},{operation},{key},{phase},{status},{msg}"
        lvl = level.lower()
        if   lvl == "info":     logger.info(rec)
        elif lvl == "warning":  logger.warning(rec)
        elif lvl == "error":    logger.error(rec)
        elif lvl == "critical": logger.critical(rec)
        else:                   logger.debug(rec)   # default
    return _log
