from dynostore.db import db

class DeviceCode(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    device_code = db.Column(db.String(64), unique=True, nullable=False)
    user_code = db.Column(db.String(16), unique=True, nullable=False)
    expires_at = db.Column(db.Integer, nullable=False)
    interval = db.Column(db.Integer, default=5)
    verified = db.Column(db.Boolean, default=False)
    username = db.Column(db.String(64), nullable=True)

