# shared_memory_bytes.py
import ctypes
import math
from utils import randbytes

MB_SIZE = 1024 * 1024

N = 5
K = 3

SIZE = 1000

# Expose functions
shared_library = ctypes.CDLL('./dispersal.so')  # Change the path accordingly
split_data = shared_library.split
split_data.argtypes = [ctypes.POINTER(ctypes.c_ubyte), ctypes.c_size_t]
split_data.restype = ctypes.POINTER(ctypes.c_char_p)  # Specify the return type as char**
write_data = randbytes(MB_SIZE * SIZE)
#write_data = b"Hello world"
#print(len(write_data))
#result_pointer = split_data((ctypes.c_ubyte * len(write_data)).from_buffer_copy(write_data), len(write_data), N, K)

# Access the data using ctypes
# Assuming the function returns a null-terminated array of strings, iterate through them

with open("test%d.bin" % SIZE, 'wb') as file:
    file.write(write_data)

#for i in range(N):
#    data = bytes(result_pointer[3])
    #print(len(data))    
    #print(data)
#    for d in data:
#        print(i,d)
    #with open("test%d.bin" % i, 'wb') as file:
    #   file.write(data)
    #break
#     #print("String {}: {}".format(i, data))

#print("-----------------------------")
# Reading from a file
# with open("test.bin", 'rb') as file:
#     read_data = file.read()
#     #print(f'Read data: {read_data}')
#     for r in read_data:
#         print(r)
#i = 0
#while True:
#    current_string = result_pointer[i]
    
    # Check for the null pointer, indicating the end of the array
    # if not current_string:
    #     break
    
    # print("String {}: {}".format(i, current_string))
    
    # # Access the bytes and decode as needed
    
    # #print("String {}: {}".format(i, current_string))
    # #byte_string = bytes(current_string)
    # ##decoded_string = byte_string.decode('utf-8')
    # #print("String {}: {}".format(i, decoded_string))
    # i += 1
# Declare the return type of the C function
#lib.split_data.restype = None

# Declare the argument type of the C function
#lib.split_data.argtypes = [ctypes.POINTER(ctypes.c_ubyte), ctypes.c_size_t]

#shared_library.testout.argtypes = [ctypes.POINTER(ctypes.c_ubyte), ctypes.POINTER(ctypes.c_int)]
#sizeout = ctypes.c_int(0)
#mem = (ctypes.c_ubyte * 20)() 
#shared_library.testout(mem, ctypes.byref(sizeout))
#print("Sizeout = " + str(sizeout.value))
#for i in range(0,sizeout.value):
#    print("Item " + str(i) + " = " + str(mem[i]))


# Load the shared library
#merger_lib = ctypes.CDLL('./merge.so')  # Change the path accordingly
#merger_lib.Recupera16.argtypes = [ctypes.c_wchar_p, ctypes.POINTER(ctypes.c_char_p)]

#strings = ["D0", "D1", "D2"]

#string_array = (ctypes.c_char_p * len(strings))()
#string_array[:] = [s.encode('utf-8') for s in strings]



#merger_lib.Recupera16("a.txt", string_array)


