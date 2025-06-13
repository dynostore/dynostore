from sqlalchemy import Column, Integer
from authlib.integrations.sqla_oauth2 import (
    OAuth2ClientMixin, OAuth2AuthorizationCodeMixin, OAuth2TokenMixin
)
from sqlalchemy import Column, Integer, Text

from . import Base



class OAuth2Client(Base, OAuth2ClientMixin):
    __tablename__ = 'oauth2_client'
    id = Column(Integer, primary_key=True)
    client_id = Column(Text, unique=True, nullable=False)
    client_secret = Column(Text, nullable=True)
    redirect_uris = Column(Text)  # must be Text column
    scope = Column(Text)
    grant_types = Column(Text)
    response_types = Column(Text)
    token_endpoint_auth_method = Column(Text)

class OAuth2AuthorizationCode(Base, OAuth2AuthorizationCodeMixin):
    __tablename__ = 'oauth2_code'
    id = Column(Integer, primary_key=True)
    user_id = Column(Integer, nullable=False)


class OAuth2Token(Base, OAuth2TokenMixin):
    __tablename__ = 'oauth2_token'
    id = Column(Integer, primary_key=True)
