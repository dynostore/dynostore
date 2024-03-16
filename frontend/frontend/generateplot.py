import sys
from numpy import genfromtxt

import matplotlib
matplotlib.use('Agg')

import matplotlib.pyplot as plt

filepath = sys.argv[1]

my_data = genfromtxt(filepath, delimiter=',')

#sprint(my_data[:, 0])

# plotting time and ecg model
plt.plot(my_data[:, 0], my_data[:, 1])
#plt.xlabel("Tiempo en segundos")
plt.ylabel("ECG")
  
# display
#plt.show()
plt.savefig(filepath + ".png")