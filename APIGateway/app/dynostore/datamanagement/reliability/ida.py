from dynostore.datamanagement.reliability.utils import build_building_blocks, inner_product, nextPrime, vandermonde_inverse, matrix_product
from dynostore.datamanagement.reliability.fragment_handler import fragment_writer, fragment_reader, fragment_reader_bytes
import pickle
import numpy as np


class Fragment:
    def __init__(self, idx, content, p, n, m, original_size):
        self.idx = idx
        self.content = content
        self.p = p
        self.n = n
        self.m = m
        self.original_size = original_size


def split_bytes(data, n, m):
    """
    Inputs:
    data: bytes to split
    n   : number of fragments after splitting the file
    m   : minimum number of fragments required to restore the file
    Output:
    a list of n fragments (as Fragment objects)
    """
    #print(data, flush=True)
    if n < 0 or m < 0:
        raise ValueError("numFragments ad numToAssemble must be positive.")

    if m > n:
        raise ValueError("numToAssemble must be less than numFragments")

    # find the prime number greater than n
    # all computations are done modulo p
    p = 251 if n < 251 else nextPrime(n)

    array_data = np.frombuffer(data, dtype=np.uint8)
    original_size = len(data)
    #print(array_data)

    del data
    top_index = len(array_data) - (len(array_data) % m)
    original_segments_arr = array_data[:top_index].reshape(-1, m)
    last_segment = array_data[top_index:]

    # fill with zeros to complete the last segment
    if len(last_segment) < m:
        last_segment = np.pad(
            last_segment, (0, m - len(last_segment)), 'constant')

    del array_data

    # insert last segment into grouped array
    original_segments_arr = np.vstack([original_segments_arr, last_segment])
    
    print(original_segments_arr.max(), flush=True)

    building_blocks = build_building_blocks(m, n, p)

    fragments = []
    #print(p)
    #p = np.uint8(p)
    
   
    for i in range(n):
        # (building_blocks[i] @ original_segments_arr.T) % p
        fragment_arr = np.dot(building_blocks[i], original_segments_arr.T) % p  #
        print(fragment_arr.max(), flush=True)
        frag=Fragment(i, fragment_arr, p, n, m, original_size)
        fragments.append(frag)

    #print(fragments, flush=True)
    return fragments




def split_bytes_v0(data, n, m):
    """
    Inputs:
    data: bytes to split
    n   : number of fragments after splitting the file
    m   : minimum number of fragments required to restore the file
    Output:
    a list of n fragments (as Fragment objects)
    """
    # print(data)
    # data = pickle.dumps(data)
    # print(data)

    if n < 0 or m < 0:
        raise ValueError("numFragments ad numToAssemble must be positive.")

    if m > n:
        raise ValueError("numToAssemble must be less than numFragments")

    # find the prime number greater than n
    # all computations are done modulo p
    p=257 if n < 257 else nextPrime(n)

    start=time.time_ns()
    original_segments=list(itertools.zip_longest(
        *(iter(data),) * m, fillvalue=0))
    end=time.time_ns()



    building_blocks=build_building_blocks(m, n, p)
    fragments=[]
    for i in range(n):
        fragment_arr=np.array([inner_product(
            building_blocks[i], original_segments[k], p) for k in range(len(original_segments))])
        

        frag=Fragment(i, fragment_arr, p, n, m)
        fragments.append(frag)

    return fragments


def split(filename, n, m):
    """
    Inputs:
    file: name of the file to split
    n   : number of fragments after splitting the file
    m   : minimum number of fragments required to restore the file
    Output:
    a list of n fragments (as Fragment objects)
    """
    if n < 0 or m < 0:
        raise ValueError("numFragments ad numToAssemble must be positive.")

    if m > n:
        raise ValueError("numToAssemble must be less than numFragments")

    # find the prime number greater than n
    # all computations are done modulo p
    p=nextPrime(n)

    # convert file to byte strings
    original_file=open(filename, "rb").read()

    # split original_file into chunks (subfiles) of length m
    original_segments=[list(original_file[i:i+m])
                         for i in range(0, len(original_file), m)]

    # for the last subfile, if the length is less than m, pad the subfile with zeros
    # to achieve final length of m
    residue=len(original_file) % m
    if residue:

        last_subfile=original_segments[-1]
        last_subfile.extend([0]*(m-residue))

    building_blocks=build_building_blocks(m, n, p)

    fragments=[]
    for i in range(n):
        fragment=[]
        for k in range(len(original_segments)):
            fragment.append(inner_product(
                building_blocks[i], original_segments[k], p))
        fragments.append(fragment)

    return fragment_writer(filename, n, m, p, original_file, fragments)


def assemble_bytes(fragments, output_filename=None):
    '''
    Input:
    fragments : a list of fragments (as Fragment objects)
    output_filename: a String for the name of the file to write
    Output:
    String represents the content of the original file
    If filename is given, the content is written to the file
    '''

    (m, n, p, fragments, original_size)=fragment_reader_bytes(fragments)
    print("m, n, p, original_size", m, n, p, original_size, flush=True)
    building_basis=[]
    fragments_matrix=[]
    for (idx, fragment) in fragments:
        building_basis.append(idx)
        fragments_matrix.append(fragment)

    inverse_building_matrix=vandermonde_inverse(building_basis, p)
    print("inverse_building_matrix", inverse_building_matrix, flush=True)
    print(fragments_matrix, flush=True)
    output_matrix=matrix_product(
        inverse_building_matrix, fragments_matrix, p)

    # each column of output matrix is a chunk of the original matrix
    original_segments=[]
    ncol=len(output_matrix[0])
    nrow=len(output_matrix)
    for c in range(ncol):
        col=[output_matrix[r][c] for r in range(nrow)]
        original_segments.append(col)
        
    residue = original_size % m
    
    
    # remove tailing zeros of the last segment
    last_segment=original_segments[-1]
    
    for i in range(residue):
        last_segment.pop()

    # combine the original_segment into original_file
    original_file=[]
    for segment in original_segments:
        #print(segment)
        original_file.extend(segment)

    # convert original_file to its content
    original_file_content=bytes(original_file)
    # data = pickle.loads(original_file_content)

    return original_file_content


def assemble(fragments_filenames, output_filename=None):
    '''
    Input:
    fragments_filenames : a list of fragments filenames
    output_filename: a String for the name of the file to write
    Output:
    String represents the content of the original file
    If filename is given, the content is written to the file
    '''

    (m, n, p, fragments)=fragment_reader(fragments_filenames)
    building_basis=[]
    fragments_matrix=[]
    for (idx, fragment) in fragments:
        building_basis.append(idx)
        fragments_matrix.append(fragment)

    inverse_building_matrix=vandermonde_inverse(building_basis, p)

    output_matrix=matrix_product(
        inverse_building_matrix, fragments_matrix, p)

    # each column of output matrix is a chunk of the original matrix
    original_segments=[]
    ncol=len(output_matrix[0])
    nrow=len(output_matrix)
    for c in range(ncol):
        col=[output_matrix[r][c] for r in range(nrow)]
        original_segments.append(col)

    # remove tailing zeros of the last segment
    last_segment=original_segments[-1]
    while last_segment[-1] == 0:
        last_segment.pop()

    # combine the original_segment into original_file
    original_file=[]
    for segment in original_segments:
        original_file.extend(segment)

    # convert original_file to its content
    original_file_content="".join(list(map(chr, original_file)))

    if output_filename:  # write the output to file
        with open(output_filename, 'wb') as fh:
            fh.write(bytes(original_file))

        print("Generated file {}".format(output_filename))
        return
    else:
        return original_file_content
