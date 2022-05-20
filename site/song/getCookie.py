import requests,json


def main():
    cookies=["879abe98b06a4d769429a79794065af3","c3c1d4d3b1467791405729457459c4cd"]
    session=requests.session()
    info={"phone":"19537806644","md5_password":cookies[1]}
    cookie=json.loads(session.post("https://music-netease.vercel.app/login/cellphone",data=info).text)["cookie"]
    with open("cookie.conf","w+") as fp:
        fp.write(cookie)

if __name__=="__main__":
    main()