from dynostore.db import db

class AccessToken(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    token = db.Column(db.String(128), unique=True, nullable=False)
    username = db.Column(db.String(64), nullable=False)
    expires_at = db.Column(db.Integer, nullable=False)

    def to_dict(self):
        return {
            "id": self.id,
            "token": self.token,
            "username": self.username,
            "expires_at": self.expires_at
        }