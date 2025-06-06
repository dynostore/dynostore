from sqlalchemy import Column, Integer, String
from flask_login import UserMixin
from sqlalchemy.ext.declarative import declarative_base
from dynostore.db import Base

class User(Base, UserMixin):
    __tablename__ = 'users'
    id = Column(Integer, primary_key=True)
    username = Column(String, unique=True)

    def get_id(self):
        return str(self.id)

    @property
    def is_authenticated(self):
        return True  # Flask-Login needs this