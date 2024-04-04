import hashlib
import time
import random
import os

"""
Create superuser token and save it in a single use file
"""
def createSuperUser():
    file = open("/app/.dynostore.txt", "r+")
    if os.path.exists("/app/.dynostore.txt") and os.path.getsize("/app/.dynostore.txt") > 0:
        return file.read()
    
    #Create hashing instance
    hashing = hashlib.sha3_256()
    
    #Get current timestamp in miliseconds
    timestamps = time.time()
    
    #Get a random number
    random_Number = random.randint(0, 1000000000)
    
    #Create a token
    base_str = str(timestamps) + str(random_Number)
    base_str = base_str.encode('utf-8')
    
    #Hash the token
    hashing.update(base_str)
    token = hashing.hexdigest()
    
    #Save the token in a temporal file
    file.write(token)
    file.close()
    
    return token
    
    