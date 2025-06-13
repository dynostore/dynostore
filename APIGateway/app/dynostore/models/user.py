from sqlalchemy import Column, Integer, String
from flask_login import UserMixin
from sqlalchemy.ext.declarative import declarative_base
from dynostore.db import Base

class User(Base, UserMixin):
    __tablename__ = 'users'
    id = Column(Integer, primary_key=True)
    username = Column(String, unique=True)
    access_token = Column(String, nullable=False)
    user_token = Column(String, nullable=False)
    api_key = Column(String, nullable=False)

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