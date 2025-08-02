
from flask_login import UserMixin
from dynostore import db


class User(db.Model, UserMixin):
    __tablename__ = 'users'
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String, unique=True)
    access_token = db.Column(db.String, nullable=False)
    user_token = db.Column(db.String, nullable=False)
    api_key = db.Column(db.String, nullable=False)

    def get_id(self):
        return str(self.id)

    @property
    def is_authenticated(self):
        return True  # Flask-Login needs this
    
    def to_dict(self):
        return {
            "username": self.username,
            "access_token": self.access_token,
            "user_token": self.user_token,
            "api_key": self.api_key
        }