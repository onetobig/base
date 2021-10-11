<?php


namespace App\Api\Helpers;

use App\Models\LeaderPrivilege;
use App\Models\LeaderUserPrivilege;
use Illuminate\Support\Facades\Redis;

trait HasLeaderPrivileges
{
    /**
     * 判断权限
     * @param string $privilege
     * @return bool
     * @author onetobig
     * @date 2020-09-15 10:29
     */
    public function hasLeaderPrivilege(string $privilege)
    {
        return $this->hasAnyLeaderPrivilege($privilege);
    }

    // 判断权限
    public function hasAnyLeaderPrivilege($privilege)
    {
        $key = $this->getLeaderPrivilegeKey();
        if (is_array($privilege)) {
            foreach ($privilege as $value) {
                if (!is_string($value)) {
                    error_msg('leader privilege array\'s value must be string');
                }
                $r = Redis::sIsMember($key, $value);
                if ($r) {
                    return true;
                }
            }
        } elseif (is_string($privilege)) {
            return Redis::sIsMember($key, $privilege);
        } else {
            error_msg('leader privilege must be string or array');
        }
        return false;
    }

    /**
     * 分配权限
     * @param $privileges
     * @return bool
     * @throws \Exception
     * @author onetobig
     * @date 2020-09-15 10:29
     */
    public function assignLeaderPrivilege($privileges)
    {
        if (!is_array($privileges)) {
            $privileges = [$privileges];
        }

        $set_privileges = LeaderPrivilege::allData();
        $set_privilege_key = array_keys($set_privileges);
        foreach ($privileges as $privilege) {
            if (!in_array($privilege, $set_privilege_key)) {
                return false;
            }
        }

        $key = $this->getLeaderPrivilegeKey();
        foreach ($privileges as $privilege) {
            Redis::sAdd($key, $privilege);
            LeaderUserPrivilege::query()
                ->firstOrCreate([
                    'leader_branch_id' => $this->leader_branch_id,
                    'leader_privilege_id' => $set_privileges[$privilege],
                    'leader_user_id' => $this->id,
                ]);
        }
    }

    /**
     * 删除权限
     * @param $privileges
     * @return bool
     * @throws \Exception
     * @author onetobig
     * @date 2020-09-15 10:29
     */
    public function removeLeaderPrivileges($privileges)
    {
        if (!is_array($privileges)) {
            $privileges = [$privileges];
        }

        $set_privileges = LeaderPrivilege::allData();
        $set_privilege_key = array_keys($set_privileges);
        foreach ($privileges as $privilege) {
            if (!in_array($privilege, $set_privilege_key)) {
                return false;
            }
        }

        $key = $this->getLeaderPrivilegeKey();
        foreach ($privileges as $privilege) {
            Redis::sRem($key, $privilege);
            $info = LeaderUserPrivilege::query()
                ->where([
                    'leader_branch_id' => $this->leader_branch_id,
                    'leader_privilege_id' => $set_privileges[$privilege],
                    'leader_user_id' => $this->id,
                ])->first();
            if ($info) {
                $info->delete();
            }
        }
    }

    /**
     * 获取所有权限数组
     * @return mixed
     * @author onetobig
     * @date 2020-09-15 14:28
     */
    public function getLeaderPrivileges()
    {
        return Redis::sMembers($this->getLeaderPrivilegeKey());
    }

    public function getLeaderPrivilegeKey()
    {
        return 'leader:user:privileges:' . $this->id;
    }
}
