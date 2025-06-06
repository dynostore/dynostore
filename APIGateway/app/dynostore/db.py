from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, scoped_session
from sqlalchemy.ext.declarative import declarative_base

engine = create_engine("sqlite:///db.sqlite")
Session = scoped_session(sessionmaker(bind=engine))
Base = declarative_base()