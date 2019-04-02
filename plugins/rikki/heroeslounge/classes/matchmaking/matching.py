from mwmatching import maxWeightMatching
import sys

edges = []

filepath = sys.argv[1]

with open(filepath, "r") as f:
    lines = f.readlines()
    for line in lines:
        edge = [int(x) for x in line.rstrip('\n').split(" ")]
        edge[2] = edge[2]*-1
        edges.append(edge)
        
matching_assignment = maxWeightMatching(edges, maxcardinality=True)

matching_edges = []
for i in range(len(matching_assignment)):
    if (i < matching_assignment[i]):
        matching_edges.append([i, matching_assignment[i]])

print matching_edges