import openai

import sys

if len( sys.argv ) < 3:
    print( "Missing arguments" )
    exit()
    

openai.api_key = sys.argv[1];

p=sys.argv[2]

response = openai.Completion.create(engine="davinci",prompt=p,max_tokens=20,temperature=1.0,top_p=0.9)


text = response.choices[0].text

print( p + text )
